<?php
/**
 * This file houses the MpmListController class.
 *
 * @package    mysql_php_migrations
 * @subpackage Controllers
 * @license    http://www.opensource.org/licenses/bsd-license.php  The New BSD License
 * @link       http://code.google.com/p/mysql-php-migrations/
 */

/**
 * The MpmListController is used to display a list of the migrations.
 *
 * @package    mysql_php_migrations
 * @subpackage Controllers
 */
class MpmListController extends MpmController
{
	
	/**
	 * Determines what action should be performed and takes that action.
	 *
	 * @uses MpmListController::displayHelp()
	 * @uses MpmListHelper::getFullList()
	 * @uses MpmListHelper::getTotalMigrations()
	 * @uses MpmCommandLineWriter::getInstance()
	 * @uses MpmCommandLineWriter::addText()
	 * @uses MpmCommandLineWriter::write()
	 * 
	 * @return void
	 */
	public function doAction()
	{
		$page = 1;
		$per_page = 30;
		
		if (isset($this->arguments[0]))
		{
			$page = $this->arguments[0];
		}
		if (isset($this->arguments[1]))
		{
			$per_page = $this->arguments[1];
		}
		
		if (!is_numeric($per_page))
		{
			$per_page = 30;
		}
		if (!is_numeric($page))
		{
			$page = 1;
		}
		$start_idx = ($page - 1) * $per_page;
		
		$list = MpmListHelper::getFullList($start_idx, $per_page);
		$total = MpmListHelper::getTotalMigrations();
		$total_pages = ceil($total / $per_page);
		$clw = MpmCommandLineWriter::getInstance();
		
		if ($total == 0)
		{
			$clw->addText('No migrations exist.');
		}
		else
		{
		    $clw->addText("WARNING: Migration numbers may not be in order due to interleaving.", 4);
		    $clw->addText(" ");
			$clw->addText("#\t\tTimestamp", 6);
			$clw->addText("=========================================", 4);
			foreach ($list as $obj)
			{
			    if (strlen($obj->id) > 1)
			    {
				    $clw->addText($obj->id . "\t" . $obj->timestamp, 6);
			    }
			    else
			    {
				    $clw->addText($obj->id . "\t\t" . $obj->timestamp, 6);
			    }
			}
			$clw->addText(" ");
			$clw->addText("Page $page of $total_pages, $total migrations in all.", 4);
		}
		
		$clw->write();
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
		$obj->addText('./migrate.php list [page] [per page]');
		$obj->addText(' ');
		$obj->addText('This command is used to display a list of all the migrations available.  Each migration is listed by number and timestamp.  You will need the migration number in order to perform an up or down migration.');
		$obj->addText(' ');
		$obj->addText('Since a project may have a large number of migrations, this command is paginated.  The page number is required.  If you do not enter it, the command will assume you want to see page 1.');
		$obj->addText(' ');
		$obj->addText('If you do not provide a per page argument, this command will default to 30 migrations per page.');
		$obj->addText(' ');
		$obj->addText('Valid Examples:');
		$obj->addText('./migrate.php list', 4);
		$obj->addText('./migrate.php list 2', 4);
		$obj->addText('./migrate.php list 1 15', 4);
		$obj->write();
	}
	
}

?>
