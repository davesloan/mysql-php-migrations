<?php
/**
 * This file houses the ExceptionalMysqli class.
 *
 * @package    mysql_php_migrations
 * @subpackage Classes
 * @license    http://www.opensource.org/licenses/bsd-license.php  The New BSD License
 * @link       http://code.google.com/p/mysql-php-migrations/
 */

/**
 * The ExceptionalMysqli class wraps the mysqli object and throws exceptions instead of triggering errors when problems occur.
 *
 * @package    mysql_php_migrations
 * @subpackage Classes
 */
class ExceptionalMysqli extends mysqli
{
	public $dryrun = false;

    /**
     * Object constructor.
     *
     * You can pass all the same parameters to this constructor as you would when instantiating a mysqli object.
     *
     * @throws MpmDatabaseConnectionException
     *
     * @uses MpmStringHelper::addSingleQuotes()
     *
     * @return ExceptionalMysqli
     */
    public function __construct()
    {
        $args = func_get_args();
        eval("parent::__construct(" . join(',', array_map('MpmStringHelper::addSingleQuotes', $args)) . ");");
        if ($this->connect_errno)
        {
            throw new MpmDatabaseConnectionException($this->connect_error);
        }
    }
    
    /**
     * Wrapper for the mysqli::query method.
     *
     * @throws MpmMalformedQueryException
     *
     * @param string $query      the SQL query to send to MySQL
     * @param int    $resultMode Either the constant MYSQLI_USE_RESULT or MYSQLI_STORE_RESULT depending on the desired behavior
	 * @param bool   $is_internal set to true for internal SQL calls which won't each SQL back when in dry run mode
     *
     * @return mysqli_result
     */
    public function query($query, $resultMode = MYSQLI_STORE_RESULT, $is_internal = false)
    {
		if ($this->dryrun) {
			if (!$is_internal) {
				// TODO
				echo "\nSQL: " . $query . "\n";
			}
			return true;

		} else {
			$result = parent::query($query, $resultMode);
			if ($this->errno)
			{
				throw new MpmMalformedQueryException($this->error);
			}
			return $result;
		}
    }

	public function internal_query($query, $resultMode = MYSQLI_STORE_RESULT) {
		return $this->query($query, $resultMode, true);
	}

    /**
     * Turns off auto commit.
     *
     * @return void
     */
    public function beginTransaction()
    {
		if (!$this->dryrun) {
			$this->autocommit(false);
		}

		return;
    }

    /**
     * Same as mysqli::query
     *
     * @uses ExceptionalMysqli::query()
	 *
	 * @param string $sql
	 * @return mysqli_result
	 */
    public function exec($sql)
    {
        return $this->query($sql);
    }

	public function internal_exec($sql) {
		return $this->internal_query($sql);
	}

	public function commit() {
		if (!$this->dryrun) {
			parent::commit();
		}
	}

	public function internal_statement_execute(mysqli_stmt $stmt) {
		if (!$this->dryrun) {
			return $stmt->execute();
		} else {
			return true;
		}
	}

}


?>
