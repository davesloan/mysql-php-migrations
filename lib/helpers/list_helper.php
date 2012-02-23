<?php
/**
 * This file houses the MpmListHelper class.
 *
 * @package    mysql_php_migrations
 * @subpackage Controllers
 * @license    http://www.opensource.org/licenses/bsd-license.php  The New BSD License
 * @link       http://code.google.com/p/mysql-php-migrations/
 */

/**
 * The MpmListHelper is used to obtain various lists related to migration files.
 *
 * @package    mysql_php_migrations
 * @subpackage Controllers
 */
class MpmListHelper
{

    /**
     * Returns the total number of migrations available.
     *
     * @uses MpmDbHelper::doSingleRowSelect()
     *
     * @return int
     */
    static function getTotalMigrations()
    {
    	$db_config = MpmDbHelper::get_db_config();
    	$migrations_table = $db_config->migrations_table;
    	$sql = "SELECT COUNT(*) AS total FROM `{$migrations_table}`";
        $obj = MpmDbHelper::doSingleRowSelect($sql);
        return $obj->total;
    }

    /**
     * Returns a full list of all migrations.
     *
     * @uses MpmDbHelper::doMultiRowSelect()
     *
     * @param int $startIdx the start index number
     * @param int $total    total number of records to return
     *
     * @return arrays
     */
    static function getFullList($startIdx = 0, $total = 30)
    {
    	$db_config = MpmDbHelper::get_db_config();
    	$migrations_table = $db_config->migrations_table;
    	$list = array();
        $sql = "SELECT * FROM `{$migrations_table}` ORDER BY `timestamp`";
        if ($total > 0)
        {
            $sql .= " LIMIT $startIdx,$total";
        }
        $list = MpmDbHelper::doMultiRowSelect($sql);
        return $list;
    }

    /**
     * Fetches a list of files and adds migrations to the database migrations table.
     *
     * @uses MpmListHelper::getListOfFiles()
     * @uses MpmListHelper::getTotalMigrations()
     * @uses MpmListHelper::getFullList()
     * @uses MpmListHelper::getTimestampArray()
     * @uses MpmDbHelper::getMethod()
     * @uses MpmDbHelper::getPdoObj()
     * @uses MpmDbHelper::getMysqliObj()
     * @uses MPM_METHOD_PDO
     *
     * @return void
     */
    static function mergeFilesWithDb()
    {
    	$db_config = MpmDbHelper::get_db_config();
    	$migrations_table = $db_config->migrations_table;
    	$files = MpmListHelper::getListOfFiles();
        $total_migrations = MpmListHelper::getTotalMigrations();
        $db_list = MpmListHelper::getFullList(0, $total_migrations);
        $file_timestamps = MpmListHelper::getTimestampArray($files);
        if (MpmDbHelper::getMethod() == MPM_METHOD_PDO)
        {
            if (count($files) > 0)
            {
                $pdo = MpmDbHelper::getPdoObj();
                $pdo->beginTransaction();
                try
                {
                    foreach ($files as $file)
                    {
                        $sql = "INSERT IGNORE INTO `{$migrations_table}` ( `timestamp`, `active`, `is_current` ) VALUES ( '{$file->timestamp}', 0, 0 )";
                        $pdo->internal_exec($sql);
                    }
                }
                catch (Exception $e)
                {
                    $pdo->rollback();
                    echo "\n\nError: " . $e->getMessage();
                    echo "\n\n";
                    exit;
                }
                $pdo->commit();
            }
            if (count($db_list))
            {
                $pdo->beginTransaction();
                try
                {
                    foreach ($db_list as $obj)
                    {
                        if (!in_array($obj->timestamp, $file_timestamps) && $obj->active == 0)
                        {
                            $sql = "DELETE FROM `{$migrations_table}` WHERE `id` = '{$obj->id}'";
                            $pdo->internal_exec($sql);
                        }
                    }
                }
                catch (Exception $e)
                {
                    $pdo->rollback();
                    echo "\n\nError: " . $e->getMessage();
                    echo "\n\n";
                    exit;
                }
                $pdo->commit();
            }
        }
        else
        {
            $mysqli = MpmDbHelper::getMysqliObj();
            $mysqli->autocommit(false);

            if (count($files) > 0)
            {
                try
                {
                    $stmt = $mysqli->prepare('INSERT IGNORE INTO `'.$migrations_table.'` ( `timestamp`, `active`, `is_current` ) VALUES ( ?, 0, 0 )');
                    foreach ($files as $file)
                    {
                        $stmt->bind_param('s', $file->timestamp);
                        $result = $mysqli->internal_statement_execute($stmt);
                        if ($result === false)
                        {
                            throw new Exception('Unable to execute query to update file list.');
                        }
                    }
                }
                catch (Exception $e)
                {
                    $mysqli->rollback();
                    $mysqli->close();
                    echo "\n\nError:", $e->getMessage(), "\n\n";
                    exit;
                }
                $mysqli->commit();
            }
            if (count($db_list))
            {
                try
                {
                    $stmt = $mysqli->prepare('DELETE FROM `'.$migrations_table.'` WHERE `id` = ?');
                    foreach ($db_list as $obj)
                    {
                        if (!in_array($obj->timestamp, $file_timestamps) && $obj->active == 0)
                        {
                            $stmt->bind_param('i', $obj->id);
                            $result = $result = $mysqli->internal_statement_execute($stmt);
                            if ($result === false)
                            {
                                throw new Exception('Unable to execute query to remove stale files from the list.');
                            }
                        }
                    }
                }
                catch (Exception $e)
                {
                    $mysqli->rollback();
                    $mysqli->close();
                    echo "\n\nError: " . $e->getMessage();
                    echo "\n\n";
                    exit;
                }
                $mysqli->commit();
            }
            $mysqli->close();
        }
    }

    /**
     * Given an array of objects (from the getFullList() or getListOfFiles() methods), returns an array of timestamps.
     *
     * @return array
     */
    static function getTimestampArray($obj_array)
    {
        $timestamp_array = array();
        foreach ($obj_array as $obj)
        {
            $timestamp_array[] = str_replace('T', ' ', $obj->timestamp);
        }
        return $timestamp_array;
    }

	/**
	 * Returns an array of objects which hold data about a migration file (timestamp, file, etc.).
	 *
	 * @uses MPM_DB_PATH
	 * @uses MpmStringHelper::getTimestampFromFilename()
	 *
	 * @param string $sort should either be old or new; determines how the migrations are sorted in the array
	 *
	 * @return array
	 */
	static public function getListOfFiles($sort = 'old') {
		$list = array();

		$exclude_list = array(
			"templates\/",
			"schema\.php$",
			"test_data\.php$"
		);

		$exclude_list_pattern = implode("|", $exclude_list);

		// SKIP_DOTS (. / ..) suppose to be included by default, but apparently not;
		$dir_iter = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator(MPM_DB_PATH,
				FilesystemIterator::KEY_AS_PATHNAME | FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::SKIP_DOTS));

		foreach ($dir_iter as $file) {
			$file_name = $file->getFilename();	// abc.js
			$file_local_path = $file->getPathName();	// /home/www/dev/jscommon/test/abc.js

			if (preg_match('/\.php$/i', $file_name) && !preg_match('/' . $exclude_list_pattern . '/i', $file_name)) {
				$timestamp = MpmStringHelper::getTimestampFromFilename($file_name);

				if ($timestamp !== null) {
					$obj = (object) array();

					$obj->timestamp = $timestamp;
					$obj->filename = $file_name;
					$obj->full_file = $file_local_path;
					$list[strtotime($timestamp)] = $obj;
				}
			}
		} // foreach

		// sort by timestamp
		if ($sort == 'new') {
			krsort($list, SORT_NUMERIC);
		} else { // 'old'
			ksort($list, SORT_NUMERIC);
		}

		return $list;
	}

	/**
	 * Returns an array of migration filenames.
	 *
	 * @uses MpmListHelper::getListOfFiles()
	 *
	 * @return array
	 */
	static public function getFiles()
	{
		$files = array();
		$list = MpmListHelper::getListOfFiles();
		foreach ($list as $obj)
		{
			$files[] = $obj->filename;
		}
		return $files;
	}

	/**
	 * Fetches a list of migrations which have already been run.
	 *
	 * @uses MpmDbHelper::doSingleRowSelect()
	 * @uses MpmDbHelper::doMultiRowSelect()
	 *
	 * @param string $latestTimestamp the current timestamp of the migration run last
	 * @param string $direction the way we are migrating; should either be up or down
	 *
	 * @return array
	 */
	static public function getListFromDb($latestTimestamp, $direction = 'up')
	{
    	$db_config = MpmDbHelper::get_db_config();
    	$migrations_table = $db_config->migrations_table;
		if ($direction == 'down')
		{
			$sql = "SELECT * FROM `{$migrations_table}` WHERE `timestamp` <= '$latestTimestamp' AND `active` = 1";
			$countSql = "SELECT COUNT(*) as total FROM `{$migrations_table}` WHERE `timestamp` <= '$latestTimestamp' AND `active` = 1";
		}
		else
		{
			$sql = "SELECT * FROM `{$migrations_table}` WHERE `timestamp` >= '$latestTimestamp' AND `active` = 1";
			$countSql = "SELECT COUNT(*) as total FROM `{$migrations_table}` WHERE `timestamp` >= '$latestTimestamp' AND `active` = 1";
		}
		$list = array();
		$countObj = MpmDbHelper::doSingleRowSelect($countSql);
		if ($countObj->total > 0)
		{
		    $results = MpmDbHelper::doMultiRowSelect($sql);
		    foreach ($results as $obj)
		    {
				$list[] = $obj->timestamp;
		    }
		}
		return $list;
	}

	/**
	 * From: http://www.php.net/manual/en/function.glob.php#106595
	 *
	 * @static
	 * @param $pattern
	 * @param int $flags
	 * @return array
	 */
	static private function glob_recursive($pattern, $flags = 0) {
		$files = glob($pattern, $flags);

		foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {
			$files = array_merge($files, self::glob_recursive($dir.'/'.basename($pattern), $flags));
		}

		return $files;
	}

	static public function get_migration_file($file_name) {
		$result = self::glob_recursive($file_name);

		if (count($result) == 0) {
			// no file found
			return false;
		} else if (count($result) > 1) {
			// found multiple files! how come!
			throw new MpmMigrationFileErrorException();
		} else {
			return $result[0];
		}
	}
}

?>
