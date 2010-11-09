<?php
/**
 * This file houses the MpmtemplateHelper class.
 *
 * @package    mysql_php_migrations
 * @subpackage Helpers
 * @license    http://www.opensource.org/licenses/bsd-license.php  The New BSD License
 * @link       http://code.google.com/p/mysql-php-migrations/
 */

/**
 * The MpmTemplateHelper class is a very basic templating class which enables custom migration, schema, test data files as well as a custom header.
 *
 * @package    mysql_php_migrations
 * @subpackage Helpers
 */
class MpmTemplateHelper
{

	/**
	 * Returns the requested template file as an array, each item in that array is a single line from the file.
	 *
	 * @uses MpmTemplateHelpger::getTemplate()
	 *
	 * @param string $file the filename of the template being requested
	 * @param array	 $vars an array of key value pairs that correspond to variables that should be replaced in the template file
	 *
	 * @return array
	 */
	static public function getTemplateAsArrayOfLines($file, $vars = array())
	{
		$contents = MpmTemplateHelper::getTemplate($file, $vars);
		$arr = explode("\n", $contents);
		return $arr;
	}

	/**
	 * Returns the requested template file as a string
	 *
	 * @uses MPM_PATH
	 *
	 * @param string $file the filename of the template being requested
	 * @param array	 $vars an array of key value pairs that correspond to variables that should be replaced in the template file
	 *
	 * @return string
	 */
	static public function getTemplate($file, $vars = array())
	{
		$db_config = $GLOBALS['db_config'];
		// has the file been customized?
		if (file_exists($db_config->db_path . $file))
		{
			$contents = file_get_contents($db_config->db_path . $file);
		}
		else
		{
			$contents = file_get_contents(MPM_PATH . '/lib/templates/' . $file);
		}
		foreach ($vars as $key => $val)
		{
			$contents = str_replace('@@' . $key . '@@', $val, $contents);
		}
		return $contents;
	}

}

?>