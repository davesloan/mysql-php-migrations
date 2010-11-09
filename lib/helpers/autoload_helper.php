<?php
/**
 * This file houses the MpmAutoloadHelper class.
 *
 * @package    mysql_php_migrations
 * @subpackage Helpers
 * @license    http://www.opensource.org/licenses/bsd-license.php  The New BSD License
 * @link       http://code.google.com/p/mysql-php-migrations/
 */

/**
 * The MpmAutoloadHelper class contains a single static method which is added to the stack of class autoloaders.  Whenever a class is instantiated which hasn't already been included, this method will be called to try to dynamically include the proper class.
 *
 * @package    mysql_php_migrations
 * @subpackage Helpers
 */
class MpmAutoloadHelper
{

	/**
	 * When part of the class autoloader stack, this method will dynamically try to located the proper class file and include it if the class is instantiated without already existing.
	 *
	 * @throws MpmClassUndefinedException
	 *
	 * @uses MPM_PATH
	 * @uses MpmStringHelper::camelToLower()
	 *
	 * @param string $class_name the name of the class being instantiated
	 *
	 * @return void
	 */
	static public function load($class_name)
	{
		// already loaded, don't need this method
        if (class_exists($class_name, false) || interface_exists($class_name, false))
        {
			return;
		}
		
        // where do we store the classes?
        $class_path = MPM_PATH . '/lib';

        // class name is coming to us in camel caps with (possibly) an Mpm prefix... remove prefix and turn into lowercase string with underscores
        $filename = MpmStringHelper::camelToLower($class_name);
        if (substr($filename, 0, 4) == 'mpm_')
        {
            $filename = substr($filename, 4, strlen($filename));
        }
        $filename .= '.php';
        
        // is it in the class path?
        if (file_exists($class_path . '/' . $filename))
        {
            require_once($class_path . '/' . $filename);
        }
		// is it in the config path?
        else if (file_exists(MPM_PATH . '/config/' . $filename))
        {
            require_once(MPM_PATH . '/config/' . $filename);
        }
        else
        {
            $dir = dir($class_path);
            while (false != ($file = $dir->read()))
            {
                if ($file != '..' && $file != '.' && is_dir($class_path . '/' . $file))
                {
                    if (file_exists($class_path . '/' . $file . '/' . $filename))
                    {
                        require_once($class_path . '/' . $file . '/' . $filename);
                    }
                }
            }
        }
        
        // make sure we've included the class
        if (false === class_exists($class_name, false))
        {
            if (false === interface_exists($class_name, false))
            {
                throw new MpmClassUndefinedException('Class or interface "' . $class_name . '" does not exist.', E_USER_ERROR);
            }
        }
        return;
	}
	
}

?>
