<?php
/**
 * This file houses the ExceptionalMysqli class.
 *
 * @package    mysql_php_migrations
 * @subpackage Classes
 * @license    http://www.opensource.org/licenses/bsd-license.php  The New BSD License
 * @link       http://code.google.com/p/mysql-php-migrations/
 */

/**
 * The ExceptionalMysqli class wraps the mysqli object and throws exceptions instead of triggering errors when problems occur.
 *
 * @package    mysql_php_migrations
 * @subpackage Classes
 */
class ExceptionalMysqli extends mysqli
{

    /**
     * Object constructor.
     *
     * You can pass all the same parameters to this constructor as you would when instantiating a mysqli object.
     *
     * @throws MpmDatabaseConnectionException
     *
     * @uses MpmStringHelper::addSingleQuotes()
     *
     * @return ExceptionalMysqli
     */
    public function __construct()
    {
        $args = func_get_args();
        eval("parent::__construct(" . join(',', array_map('MpmStringHelper::addSingleQuotes', $args)) . ");");
        if ($this->connect_errno)
        {
            throw new MpmDatabaseConnectionException($this->connect_error);
        }
    }
    
    /**
     * Wrapper for the mysqli::query method.
     *
     * @throws MpmMalformedQueryException
     *
     * @param string $query      the SQL query to send to MySQL
     * @param int    $resultMode Either the constant MYSQLI_USE_RESULT or MYSQLI_STORE_RESULT depending on the desired behavior
     *
     * @return mysqli_result
     */
    public function query($query, $resultMode = MYSQLI_STORE_RESULT)
    {
        $result = parent::query($query, $resultMode);
        if ($this->errno)
        {
            throw new MpmMalformedQueryException($this->error);
        }
        return $result;
    }
    
    /**
     * Turns off auto commit.
     *
     * @return void
     */
    public function beginTransaction()
    {
        $this->autocommit(false);
        return;
    }

    /**
     * Same as mysqli::query
     *
     * @uses ExceptionalMysqli::query()
     *
     * @return mysqli_result
     */
    public function exec($sql)
    {
        return $this->query($sql);
    }

}


?>
