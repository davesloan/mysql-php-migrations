<?php
/**
 * This file houses the MpmHelpController class.
 *
 * @package    mysql_php_migrations
 * @subpackage Controllers
 * @license    http://www.opensource.org/licenses/bsd-license.php  The New BSD License
 * @link       http://code.google.com/p/mysql-php-migrations/
 */

/**
 * The MpmHelpController is used to display help about the commands available as well as more specific help for individual commands.
 *
 * @package    mysql_php_migrations
 * @subpackage Controllers
 */
class MpmHelpController extends MpmController
{
	
	/**
	 * Determines what action should be performed and takes that action.
	 *
	 * @uses MpmHelpController::displayHelp()
	 * @uses MpmStringHelper::strToCamel()
	 * @uses MpmAutoloadHelper::load()
	 * 
	 * @return void
	 */
	public function doAction()
	{
		if (count($this->arguments) == 0)
		{
			return $this->displayHelp();
		}
		else
		{
			$controller_name = $this->arguments[0];
			$class_name = ucwords(MpmStringHelper::strToCamel('mpm_' . strtolower($controller_name) . '_controller'));
			try
			{
				MpmAutoloadHelper::load($class_name);
			}
			catch(Exception $e)
			{
				return $this->displayHelp();
			}
			$obj = new $class_name();
			return $obj->displayHelp();
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
		$obj->addText('The Following Commands Are Available:');
		$obj->addText('add    - add a new migration', 4);
		$obj->addText('build  - builds the database', 4);
		$obj->addText('down   - roll down to a previous migration', 4);
		$obj->addText('help   - get more specific help about individual commands', 4);
		$obj->addText('init   - initialize the migrations', 4);
		$obj->addText('latest - roll up to the latest migration', 4);
		$obj->addText('list   - list all migrations', 4);
		$obj->addText('run    - runs a single migration', 4);
		$obj->addText('status - show the current migration', 4);
		$obj->addText('up     - roll up to a future migration', 4);
		$obj->addText(' ');
		
		$obj->addText('For specific help for an individual command, type:');
		$obj->addText('./migrate.php help [command]', 4);
		$obj->write();
	}
	
}


?>
