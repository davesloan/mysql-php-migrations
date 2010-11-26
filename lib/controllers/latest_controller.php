<?php
/**
 * This file houses the MpmLatestController class.
 *
 * @package    mysql_php_migrations
 * @subpackage Controllers
 * @license    http://www.opensource.org/licenses/bsd-license.php  The New BSD License
 * @link       http://code.google.com/p/mysql-php-migrations/
 */

/**
 * The MpmLatestController is used to migrate up to the latest version.
 *
 * @package    mysql_php_migrations
 * @subpackage Controllers
 */
class MpmLatestController extends MpmController
{

	/**
	 * Determines what action should be performed and takes that action.
	 *
	 * @uses MpmLatestController::displayHelp()
	 * @uses MpmDbHelper::test()
	 * @uses MpmMigrationHelper::getMigrationCount()
	 * @uses MpmCommandLineWriter::getInstance()
	 * @uses MpmMigrationHelper::getLatestMigration()
	 * @uses MpmUpController::doAction()
	 *
	 * @param bool $quiet supresses certain text when true
	 *
	 * @return void
	 */
	public function doAction($quiet = false)
	{
		// make sure we're init'd
		MpmDbHelper::test();

		// are we forcing this?
		$forced = '';
		if (isset($this->arguments[0]) && strcasecmp($this->arguments[0], '--force') == 0)
		{
		    $forced = '--force';
		}

		try
		{
			$total_migrations = MpmMigrationHelper::getMigrationCount();
			if ($total_migrations == 0)
			{
				$clw = MpmCommandLineWriter::getInstance();
				$clw->addText('No migrations exist.');
				$clw->write();
			}
			else
			{
				$to_id = MpmMigrationHelper::getLatestMigration();
				$obj = new MpmUpController('up', array ( $to_id, $forced ));
	    		$obj->doAction($quiet);
			}
		}
		catch (Exception $e)
		{
			echo "\n\nERROR: " . $e->getMessage() . "\n\n";
			exit;
		}
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
		$obj->addText('./migrate.php latest [--force]');
		$obj->addText(' ');
		$obj->addText('This command is used to migrate up to the most recent version.  No arguments are required.');
		$obj->addText(' ');
		$obj->addText('If the --force option is provided, then the script will automatically skip over any migrations which cause errors and continue migrating forward.');
		$obj->addText(' ');
		$obj->addText('Valid Examples:');
		$obj->addText('./migrate.php latest', 4);
		$obj->addText('./migrate.php latest --force', 4);
		$obj->write();
	}

}

?>
