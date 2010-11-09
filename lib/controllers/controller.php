<?php
/**
 * This file houses the MpmController class.
 *
 * @package    mysql_php_migrations
 * @subpackage Classes
 * @license    http://www.opensource.org/licenses/bsd-license.php  The New BSD License
 * @link       http://code.google.com/p/mysql-php-migrations/
 */

/**
 * The MpmController is the abstract parent class to all other controllers.
 *
 * @package    mysql_php_migrations
 * @subpackage Classes
 */
abstract class MpmController
{

	/**
	 * An array of command line arguments (minus the first two elements which should already be shifted off from the MpmControllerFactory).
	 *
	 * @var array
	 */
	protected $arguments;
	
	/** 
	 * The current command being issued.
	 *
	 * @var string
	 */
	protected $command;
	
	/** 
	 * Object constructor.
	 * 
	 * @uses MpmDbHelper::test()
	 * @uses MpmListHelper::mergeFilesWithDb()
	 *
	 * @param array $arguments an array of command line arguments (minus the first two elements which should already be shifted off from the MpmControllerFactory)
	 *
	 * @return MpmController
	 */
	public function __construct($command = 'help', $arguments = array())
	{
		$this->arguments = $arguments;
		$this->command = $command;
		if ($command != 'help' && $command != 'init')
		{
            MpmDbHelper::test();
    		MpmListHelper::mergeFilesWithDb();
		}
	}
	
	/**
	 * Determines what action should be performed and takes that action.
	 *
	 * @return void
	 */
	abstract public function doAction();
	
	/**
	 * Displays the help page for this controller.
	 * 
	 * @return void
	 */
	abstract public function displayHelp();
	
}

?>
