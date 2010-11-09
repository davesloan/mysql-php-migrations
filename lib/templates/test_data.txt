<?php
/**
 * This file houses the MpmTestData class.
 *
 * This file may be deleted if you do not wish to add test data to your database after a build.
 *
 * @package    mysql_php_migrations
 * @subpackage Classes
 * @license    http://www.opensource.org/licenses/bsd-license.php  The New BSD License
 * @link       http://code.google.com/p/mysql-php-migrations/
 */

/**
 * The MpmTestData class is used to add test data after successfully building an initial database structure.
 *
 * @package    mysql_php_migrations
 * @subpackage Classes
 */
class MpmTestData extends MpmSchema
{

	public function build()
 	{
		/* Add the queries needed to insert test data into the initial build of your database.
		*
		* EX:
		*
		* $this->dbObj->exec("INSERT INTO `testing` (id, username, password) VALUES (1, 'my_username', 'my_password')");
		*/
	}

}

?>