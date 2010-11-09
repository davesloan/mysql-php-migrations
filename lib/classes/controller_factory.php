<?php
/**
 * This file houses the MpmControllerFactory class.
 *
 * @package    mysql_php_migrations
 * @subpackage Classes
 * @license    http://www.opensource.org/licenses/bsd-license.php  The New BSD License
 * @link       http://code.google.com/p/mysql-php-migrations/
 */

/**
 * The MpmControllerFactory reads the command line arguments, determines which controller is needed, and returns that controlller object.
 *
 * @package    mysql_php_migrations
 * @subpackage Classes
 */
class MpmControllerFactory
{
	
	/**
	 * Given an array of command line arguments ($argv), determines the controller needed and returns that object.
	 *
	 * @uses MpmStringHelper::strToCamel()
	 *
	 * @return MpmController
	 */
	static public function getInstance($argv)
	{
		$script_name = array_shift($argv);
		$controller_name = array_shift($argv);
		if ($controller_name == null)
		{
			$controller_name = 'help';
		}
		$class_name = ucwords(MpmStringHelper::strToCamel('mpm_' . strtolower($controller_name) . '_controller'));
		$obj = new $class_name($controller_name, $argv);
		return $obj;
	}
	
}

?>
