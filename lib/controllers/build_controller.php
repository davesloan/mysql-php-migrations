<?php
/**
 * This file houses the MpmBuildController class.
 *
 * @package    mysql_php_migrations
 * @subpackage Controllers
 * @license    http://www.opensource.org/licenses/bsd-license.php  The New BSD License
 * @link       http://code.google.com/p/mysql-php-migrations/
 */

/**
 * The MpmBuildController is used to build a database schema from the ground up.
 *
 * @package    mysql_php_migrations
 * @subpackage Controllers
 */
class MpmBuildController extends MpmController
{
	/**
	 * @var MpmCommandLineWriter
	 */
	protected $clw;

	/**
	 * Determines what action should be performed and takes that action.
	 *
	 * @uses MpmDbHelper::test()
	 * @uses MpmCommandLineWriter::getInstance()
	 * @uses MpmCommandLineWriter::addText()
	 * @uses MpmCommandLineWriter::write()
	 * @uses MpmCommandLineWriter::writeHeader()
	 * @uses MpmCommandLineWriter::writeFooter()
	 * @uses MpmBuildController::build()
	 * @uses MPM_DB_PATH
	 *
	 * @return void
	 */
	public function doAction()
	{
		// make sure system is init'ed
		MpmDbHelper::test();

		$this->clw = MpmCommandLineWriter::getInstance();
        $with_data = $forced = $dryrun = false;

        // are we adding a schema file?
		if (isset($this->arguments[0])) {
			if ($this->arguments[0] == 'add') {
				$this->add();
				// stop here
				exit;

			} else if ($this->arguments[0] == 'with_data') {
				$with_data = true;

				// remove the first 'with_data'
				array_shift($this->arguments);
			}
		}

		// parse other optional arguments
		list($forced, $dryrun) = $this->parse_options($this->arguments);

        // make sure the schema file exists
        if (!file_exists(MPM_DB_PATH . 'schema.php'))
        {
            $this->clw->addText('The schema file does not exist.  Run this command with the "add" argument to create one (only a stub).');
            $this->clw->write();
            exit;
        }
        // make sure the test data file exists
        if ($with_data == true && !file_exists(MPM_DB_PATH . 'test_data.php'))
        {
            $this->clw->addText('The test data file does not exist.  Run this command with the "add" argument to create one (only a stub).');
            $this->clw->write();
            exit;
        }

        $this->clw->writeHeader();

        if (!$forced)
        {
		    echo "\nWARNING:  IF YOU CONTINUE, ALL TABLES IN YOUR DATABASE WILL BE ERASED!";
		    echo "\nDO YOU WANT TO CONTINUE? [y/N] ";
		    $answer = fgets(STDIN);
		    $answer = trim($answer);
		    $answer = strtolower($answer);
		    if (empty($answer) || substr($answer, 0, 1) == 'n')
		    {
			    echo "\nABORTED!\n\n";
			    $this->clw->writeFooter();
			    exit;
		    }
		}

        echo "\n";
        $this->build($with_data, $this->arguments);

        $this->clw->writeFooter();
        exit;
	}

	/**
	 * Create an empty stub for the schema.php file
	 */
	protected function add() {
		// make sure the schema file doesn't exist
		if (file_exists(MPM_DB_PATH . 'schema.php') || file_exists(MPM_DB_PATH . 'test_data.php'))
		{
			$this->clw->addText('The schema and/or test data files already exist.  Delete them first if you want to use this option.');
			$this->clw->write();
			exit;
		}
		$file = MpmTemplateHelper::getTemplate('schema.txt');
		$test_data_file = MpmTemplateHelper::getTemplate('test_data.txt');

		$fp = fopen(MPM_DB_PATH . 'schema.php', "w");
		if ($fp == false)
		{
			echo "\nUnable to write to file.  Initialization failed!\n\n";
			exit;
		}
		$success = fwrite($fp, $file);
		if ($success == false)
		{
			echo "\nUnable to write to file.  Initialization failed!\n\n";
			exit;
		}
		fclose($fp);

		$fp = fopen(MPM_DB_PATH . 'test_data.php', "w");
		if ($fp == false)
		{
			echo "\nUnable to write to file.  Initialization failed!\n\n";
			exit;
		}
		$success = fwrite($fp, $test_data_file);
		if ($success == false)
		{
			echo "\nUnable to write to file.  Initialization failed!\n\n";
			exit;
		}
		fclose($fp);

		$this->clw->addText('File ' . MPM_DB_PATH . 'schema.php has been created.');
		$this->clw->addText('File ' . MPM_DB_PATH . 'test_data.php has been created.');
		$this->clw->write();
	}

	/**
	 * Does the actual task of destroying and rebuilding the database from the ground up.
	 *
	 * @uses MpmSchema::destroy()
	 * @uses MpmSchema::reloadMigrations()
	 * @uses MpmSchema::build()
	 * @uses MpmLatestController::doAction()
	 * @uses MPM_DB_PATH
	 * @param bool $with_data	whether or not to run the test_data.php file after build
	 * @param array $arguments	command line arguments to be passed to 'up' action controller
	 */
	public function build($with_data, $arguments)
	{
	    require_once(MPM_DB_PATH . 'schema.php');
	    $obj = new MpmInitialSchema();
	    $obj->destroy();
	    echo "\n";
	    $obj->reloadMigrations();
		echo "\n", 'Building initial database schema... ';
	    $obj->build();
		echo 'done.', "\n\n", 'Applying migrations... ';
		try
		{
			$total_migrations = MpmMigrationHelper::getMigrationCount();
			if ($total_migrations == 0)
			{
				echo "no migrations exist.";
			}
			else
			{
				$to_id = MpmMigrationHelper::getLatestMigration();
				$obj = new MpmUpController('up', array_merge(array($to_id), $arguments));
	    		$obj->doAction();
			}
		}
		catch (Exception $e)
		{
			echo "\n\nERROR: " . $e->getMessage() . "\n\n";
			exit;
		}
		if ($with_data)
		{
	    	require_once(MPM_DB_PATH . 'test_data.php');
			echo "\n\nInserting test data... ";
			$test_data_obj = new MpmTestData();
			$test_data_obj->build();
			echo 'done.';
		}
		echo "\n\n", 'Database build complete.', "\n";
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
		$obj->addText('./migrate.php build [--force|add]');
		$obj->addText(' ');
		$obj->addText('This command is used to build the database.  If a schema.php file is found in the migrations directory, the MpmSchema::Build() method will be called.  Then, all migrations will be run against the database.');
		$obj->addText(' ');
		$obj->addText('Use the "add" argument to create an empty stub for the schema.php file.  You can then add your own query statements.  This will also add a filed called test_data.php.  You can add queries to this file to insert test data after a build.');
		$obj->addText(' ');
		$obj->addText('Use the "with_data" argument to run the test_data.php file after the database has been rebuilt.  This allows you to automatically insert fresh new test data into the system.');
		$obj->addText(' ');
		$obj->addText('If you use the "--force" argument instead of the "add" argument, you will not be prompted to confirm the action (good for scripting a build process).');
		$obj->addText(' ');
		$obj->addText('WARNING: THIS IS A DESTRUCTIVE ACTION!!  BEFORE THE DATABASE IS BUILT, ALL TABLES CURRENTLY IN THE DATABASE ARE REMOVED!');
		$obj->addText(' ');
		$obj->addText('Valid Examples:');
		$obj->addText('./migrate.php build add', 4);
		$obj->addText('./migrate.php build with_data', 4);
		$obj->addText('./migrate.php build with_data [--force|-f] [--dry-run|-p]', 4);
		$obj->write();
	}

}

?>
