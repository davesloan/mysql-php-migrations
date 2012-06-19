<?php
require_once(MPM_PATH."/lib/yaml/spyc.php");
/**
 * This file houses the MpmInitController class.
 *
 * @package    mysql_php_migrations
 * @subpackage Controllers
 * @license    http://www.opensource.org/licenses/bsd-license.php  The New BSD License
 * @link       http://code.google.com/p/mysql-php-migrations/
 */

/**
 * The MpmInitController initializes the system so that migrations can start happening.
 *
 * @package    mysql_php_migrations
 * @subpackage Controllers
 */
class MpmInitController extends MpmController
{

	/**
	 * Determines what action should be performed and takes that action.
	 *
	 * @uses MPM_PATH
	 * @uses MPM_METHOD_PDO
	 * @uses MPM_METHOD_MYSQLI
	 * @uses MpmDbHelper::checkForDbTable()
	 * @uses MpmDbHelper::getDbObj()
	 * @uses MpmDbHelper::getMethod()
	 * @uses MpmInitController::displayHelp()
	 * @uses MpmCommandLineWriter::getInstance()
	 * @uses MpmCommandLineWriter::writeHeader()
	 * @uses MpmCommandLineWriter::writeFooter()
	 * @uses MpmBuildController::build()
	 *
	 * @return void
	 */
	public function doAction()
	{
		$user = '';
		$dbname = '';
		$port = '';
		$db_path = '';
		$method = 0;

		$clw = MpmCommandLineWriter::getInstance();
		$clw->writeHeader();
		echo "Defaults are in brackets ([]).  To accept the default, simply press ENTER.\n\n";
		echo "Running _yaml_parse...";
		echo "\n";
		$this->db_config = (object) array();

		if (file_exists(MPM_PATH . '/config/db_config.yml'))
		{
			echo "\nWARNING:  IF YOU CONTINUE, YOUR EXISTING MIGRATION SETUP WILL BE ERASED!";
			echo "\nThis will not affect your existing migrations or database, but \ncould cause your future migrations to fail.";
			echo "\nDO YOU WANT TO CONTINUE? [y/N] ";
			$answer = fgets(STDIN);
			$answer = trim($answer);
			$answer = strtolower($answer);
			if (empty($answer) || substr($answer, 0, 1) == 'n')
			{
				echo "\nABORTED!\n\n";
				$clw->writeFooter();
				exit;
			}
			else
			{
					$this->_yaml_parse();
			}
		}

		echo "\nEnter a name to use for the table that will hold your migration data [";
		if (isset($db_config) && isset($db_config->migrations_table))
		{
		    echo $db_config->migrations_table;
		}
		else
		{
		    echo 'mpm_migrations';
		}
		echo ']: ';
		$migrations_table = fgets(STDIN);
		$migrations_table = trim($migrations_table);
		if (empty($migrations_table))
		{
    		if (isset($db_config) && isset($db_config->migrations_table))
    		{
    		    $migrations_table = $db_config->migrations_table;
    		}
    		else
    		{
    			$migrations_table = 'mpm_migrations';
    		}
		}


		do
		{
			echo "\nWhich method would you like to use to connect to\nthe database?  ".MPM_METHOD_PDO."=PDO or ".MPM_METHOD_MYSQLI."=MySQLi";
			if (isset($db_config))
			{
			    echo " [" . $db_config->method . "]";
			}
			echo ": ";
			$method = fgets(STDIN);
			$method = trim($method);
			if (!is_numeric($method))
			{
			    $method = 0;
			}
			if (empty($method) && isset($db_config))
			{
			    $method = $db_config->method;
			}
		} while ($method < MPM_METHOD_PDO || $method > MPM_METHOD_MYSQLI || $method == 0);

		echo "\nEnter your MySQL database hostname or IP address [";
		if (isset($db_config))
		{
		    echo $db_config->host;
		}
		else
		{
		    echo 'localhost';
		}
		echo ']: ';
		$host = fgets(STDIN);
		$host = trim($host);
		if (empty($host))
		{
    		if (isset($db_config))
    		{
    		    $host = $db_config->host;
    		}
    		else
    		{
    			$host = 'localhost';
    		}
		}

		while (empty($port))
		{
			echo "\nEnter your MySQL database port [";
		    if (isset($db_config))
		    {
		        echo $db_config->port;
		    }
		    else
		    {
		        echo '3306';
		    }
		    echo ']: ';

			$port = fgets(STDIN);
			$port = trim($port);
			if (empty($port))
			{
				$port = 3306;
			}
			if (!is_numeric($port))
			{
				$port = '';
			}
		}

		while (empty($user))
		{
			echo "\nEnter your MySQL database username";
		    if (isset($db_config))
		    {
		        echo ' [', $db_config->user, ']';
		    }
		    echo ': ';
			$user = fgets(STDIN);
			$user = trim($user);
			if (empty($user) && isset($db_config))
			{
			    $user = $db_config->user;
			}
		}

		echo "\nEnter your MySQL database password (enter - for no password) [";
		if (isset($db_config))
		{
		    echo $db_config->pass;
		}
		echo ']: ';
		$pass = fgets(STDIN);
		$pass = trim($pass);
		if (empty($pass) && isset($db_config))
		{
		    $pass = $db_config->pass;
		}
		else if ($pass == '-')
		{
		    $pass = '';
		}


		while (empty($dbname))
		{
			echo "\nEnter your MySQL database name";
			if (isset($db_config))
			{
			    echo ' [', $db_config->name, ']';
			}
			echo ': ';
			$dbname = fgets(STDIN);
			$dbname = trim($dbname);
			if (empty($dbname) && isset($db_config))
			{
			    $dbname = $db_config->name;
			}
		}

		echo "\nEnter the directory where you'd like to store your\nmigration files [";
		if (isset($db_config))
		{
		    echo $db_config->db_path;
    	}
    	else
    	{
    	    echo MPM_PATH . '/db/';
    	}
    	echo ']: ';
		$db_path = fgets(STDIN);
		$db_path = trim($db_path);
		if (empty($db_path) && isset($db_config))
		{
		    $db_path = $db_config->db_path;
		}
		else if (empty($db_path) && !isset($db_config))
		{
		    $db_path = MPM_PATH . '/db/';
		}
		if (substr($db_path, strlen($db_path) - 1, 1) != '/')
		{
		    $db_path .= '/';
		}

		$method = (int) $method;

		if (file_exists($db_path . 'schema.php'))
		{
		    echo "\nPerform build of database after initialization (builds schema\nand runs all existing migrations) [y/N]: ";
		    $do_build = fgets(STDIN);
		    $do_build = trim($do_build);
		    $doBuild = false;
            if (strcasecmp(substr($do_build, 0, 1), 'y') == 0)
            {
                $doBuild = true;
            }
		}
		$this->db_config->host = $host;
		$this->db_config->port = $port;
		$this->db_config->user = $user;
		$this->db_config->pass = $pass;
		$this->db_config->name = $dbname;
		$this->db_config->db_path = $db_path;
		$this->db_config->method = $method;
		$this->db_config->migrations_table = $migrations_table;
		$this->output = Spyc::YAMLDump($this->db_config);

		$success = $this->_yaml_write();
		if ($success == false)
		{
			echo "\nUnable to write to file.  Initialization failed!\n\n";
			exit;
		}
		else echo "Wrote successfully!";

		//require(MPM_PATH . '/config/db_config.php');
		$GLOBALS['db_config'] = $this->db_config;

		echo "\nConfiguration saved... looking for existing migrations table... ";

		try
		{
			if (false === MpmDbHelper::checkForDbTable())
			{
				echo "not found.\n";
				echo "Creating migrations table... ";
				$sql1 = "CREATE TABLE IF NOT EXISTS `{$migrations_table}` ( `id` INT(11) NOT NULL AUTO_INCREMENT, `timestamp` DATETIME NOT NULL, `active` TINYINT(1) NOT NULL DEFAULT 0, `is_current` TINYINT(1) NOT NULL DEFAULT 0, PRIMARY KEY ( `id` ) ) ENGINE=InnoDB";
				$sql2 = "CREATE UNIQUE INDEX `TIMESTAMP_INDEX` ON `{$migrations_table}` ( `timestamp` )";

				if (MpmDbHelper::getMethod() == MPM_METHOD_PDO)
				{
    				$pdo = MpmDbHelper::getDbObj();
    				$pdo->beginTransaction();
    				try
    				{
    					$pdo->exec($sql1);
    					$pdo->exec($sql2);
    				}
    				catch (Exception $e)
    				{
    					$pdo->rollback();
    					echo "failure!\n\n" . 'Unable to create required ' . $migrations_table . ' table:' . $e->getMessage();
    					echo "\n\n";
    					exit;
    				}
    				$pdo->commit();
			    }
			    else
			    {
			        $mysqli = MpmDbHelper::getDbObj();
			        $mysqli->query($sql1);
			        if ($mysqli->errno)
			        {
    					echo "failure!\n\n" . 'Unable to create required ' . $migrations_table . ' table:' . $mysqli->error;
    					echo "\n\n";
    					exit;
			        }
		            $mysqli->query($sql2);
			        if ($mysqli->errno)
			        {
    					echo "failure!\n\n" . 'Unable to create required ' . $migrations_table . ' table:' . $mysqli->error;
    					echo "\n\n";
    					exit;
			        }
			    }
				echo "done.\n\n";
			}
			else
			{
				echo "found.\n\n";
			}

		}
		catch (Exception $e)
		{
			echo "failure!\n\nUnable to complete initialization: " . $e->getMessage() . "\n\n";
			echo "Check your database settings and re-run init.\n\n";
			exit;
		}

		if (isset($doBuild) && $doBuild === true)
		{
		    $obj = new MpmBuildController();
		    $obj->build();
		    echo "\n\n";
		}

		echo "Initalization complete!  Type 'php migrate.php help' for a list of commands.\n\n";
		$clw->writeFooter();
		exit;
	}

	/**
	 * Displays the help page for this controller.
	 *
	 * @uses MpmCommandLineWriter::getInstance()
	 * @uses MpmCommandLineWriter::addText()
	 * @uses MpmCommandLineWriter::write()
	 *
	 * @return void
	 */
	public function displayHelp()
	{
		$obj = MpmCommandLineWriter::getInstance();
		$obj->addText('./migrate.php init');
		$obj->addText(' ');
		$obj->addText('This command is used to initialize the migration system for use with your particular deployment.  After you have modified the /config/db.php configuration file appropriately, you should run this command to setup the initial tracking schema and add your username to the migraiton archive.');
		$obj->addText(' ');
		$obj->addText('Example:');
		$obj->addText('./migrate.php init jdoe', 4);
		$obj->write();
	}


	private function _yaml_write()
	{
		$fp = fopen(MPM_PATH . '/config/db_config.yml', "w");
		if ($fp == false)
		{
			echo "\nUnable to write to file.  Initialization failed!\n\n";
			exit(1);
		}
		$success = fwrite($fp, $this->output);
		if ($success) {
			fclose($fp);
		}
		else 
		{
			echo "\n Unable to write to file. Initialization failed!\n\n";
			exit(1);
		}
		return $success;
	}

	/**
	*
	* load the YAML database file into the db_config object used globally
	*
	* this function (and _yaml_write) require the spyc YAML php library to be in lib/yaml/ 
	*
	*		Switching to an environment setup would build the db YAML file like so:
	*
	*		development:
	*			host = localhost
	*			...
	*		production:
	*			host = mysqlserver.somedomain.com
	*		... 
	*		etc
	*
	*		When YAMLLoad loads that file into a variable, it creates a two-deep array:
	*		layer 0 would look like this:
	*		[0] => development
	*		[1] => production
	*
	*		layer 1:
	*		[host] => localhost
	*
	*		so to implement environments, one would simply create a function which sets a global variable for the preferred environment
	*		such as ./migrate.php env, which would take the name of the environment to use.
	*
	*		then simply iterate through the two-deep array and check the key against the set environment, like so:
	*		foreach ($config_file as $k => $v)
	*		{
	*			if ($k == $GLOBALS['environment'])
	*				$this->db_config assignment code here
	*		}
	*
	*/
	private function _yaml_parse()
	{
		$debug  = true;
		$clw = MpmCommandLineWriter::getInstance();
		if (file_exists(MPM_PATH . '/config/db_config.yml'))
		{
			// TODO:
			/*
			 *	Split prompt and load into two functions
			 *	Allow for environment switching via commandline flag
			 *
			*/
				$orig_config = Spyc::YAMLLoad(MPM_PATH . '/config/db_config.yml');
				$this->db_config->host = $orig_config['host'];
				$this->db_config->port = $orig_config['port'];
				$this->db_config->user = $orig_config['user'];
				$this->db_config->pass = $orig_config['pass'];
				$this->db_config->name = $orig_config['name'];
				$this->db_config->db_path = $orig_config['db_path'];
				$this->db_config->method = $orig_config['method'];
				$this->db_config->migrations_table = $orig_config['migrations_table'];
				$GLOBALS['db_config'] = $this->db_config;
		}
		else
		{
			echo "ERROR: Could not read database file.";
			exit(1);
		}
	}

}

?>
