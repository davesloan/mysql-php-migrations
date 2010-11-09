<?php
/**
 * This file houses the MpmStatusController class.
 *
 * @package    mysql_php_migrations
 * @subpackage Controllers
 * @license    http://www.opensource.org/licenses/bsd-license.php  The New BSD License
 * @link       http://code.google.com/p/mysql-php-migrations/
 */

/**
 * The MpmStatusController is used to display the latest migration.
 *
 * @package    mysql_php_migrations
 * @subpackage Controllers
 */
class MpmStatusController extends MpmController
{
	
	/**
	 * Determines what action should be performed and takes that action.
	 *
	 * @uses MpmDbHelper::test()
	 * @uses MpmMigrationHelper::getCurrentMigrationTimestamp()
	 * @uses MpmMigrationHelper::getCurrentMigrationNumber()
	 * @uses MpmListHelper::getFullList()
	 * @uses MpmCommandLineWriter::getInstance()
	 * @uses MpmCommandLineWriter::writeHeader()
	 * @uses MpmCommandLineWriter::writeFooter()
	 *
	 * @return void
	 */
	public function doAction()
	{
		// make sure we're init'd
		MpmDbHelper::test();
		
		// get latest timestamp
		$latest = MpmMigrationHelper::getCurrentMigrationTimestamp();
		
		// get latest number
		$num = MpmMigrationHelper::getCurrentMigrationNumber();
		
		// get list of migrations
		$list = MpmListHelper::getFullList();
		
		// get command line writer
		$clw = MpmCommandLineWriter::getInstance();
		$clw->writeHeader();
		
		if (empty($latest))
		{
			echo "You have not performed any migrations yet.";
		}
		else
		{
			echo "You are currently on migration $num -- " . $latest . '.';
		}
		echo "\n";
		$clw->writeFooter();
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
		$obj->addText('./migrate.php status');
		$obj->addText(' ');
		$obj->addText('This command is used to display the current migration you are on and lists any pending migrations which would be performed if you migrated to the most recent version of the database.');
		$obj->addText(' ');
		$obj->addText('Valid Example:');
		$obj->addText('./migrate.php status', 4);
		$obj->write();
	}
	
}

?>
