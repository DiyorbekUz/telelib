<?php

/**
 * @package DiyorbekUz\Telelib\src\config
 * @author DiyorbekDev
 */

/**
 * Configuration for database connection
 *
 */

 //PDO connection
class PDO extends \PDO{
    public function __construct($hostname, $username, $password, $database, $port = '3306') {
        try {
            $this->connection = new \PDO("mysql:host=" . $hostname . ";port=" . $port . ";dbname=" . $database, $username, $password, array(\PDO::ATTR_PERSISTENT => true));
        } catch(\PDOException $e) {
            throw new \Exception('Failed to connect to database. Reason: \'' . $e->getMessage() . '\'');
        }
    }
}

//MySql Connection
class MySQL extends \MySQLi
{
	public function __construct($hostname, $username, $password, $dbname)
	{
		return parent::__construct($hostname, $username, $password, $dbname);
	}
}


//SqLite connection
class SQLite extends \SQLite3
{
	function __construct($dbname) //database.(db, sqlite)
	{
		return parent::__construct($dbname);
	}
}

?>