<?php
/**
 * This file houses the MpmMigration class.
 *
 * @package    mysql_php_migrations
 * @subpackage Controllers
 * @license    http://www.opensource.org/licenses/bsd-license.php  The New BSD License
 * @link       http://code.google.com/p/mysql-php-migrations/
 */

/**
 * The MpmMigration is an abstract template class used as the parent to all migration classes.
 *
 * @package    mysql_php_migrations
 * @subpackage Controllers
 */
abstract class MpmMigration
{
	/**
	 * Migrates the database up.
	 * 
	 * @param PDO $pdo a PDO object
	 *
	 * @return void
	 */
	abstract public function up(PDO &$pdo);
	
	/** 
	 * Migrates down (reverses changes made by the up method).
	 *
	 * @param PDO $pdo a PDO object
	 *
	 * @return void
	 */
	abstract public function down(PDO &$pdo);
}


?>