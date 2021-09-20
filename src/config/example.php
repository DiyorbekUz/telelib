<?php

//PDO MySql connection 
define('DB_HOSTNAME', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_DATABASE', 'pdo_test');
//require_once(dirname(__FILE__).'/src/config.php');
require_once('cinfig.php');
$db = new mPDO(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

$query = $db->query("SELECT * FROM `customer` WHERE `name` LIKE 'John'");
if ($query->rows) {
    var_dump($query->row['email']);
}


//SqLite connection

$db = new SQLite3('database.db');

$results = $db->query('SELECT bar FROM foo');
while ($row = $results->fetchArray()) {
    var_dump($row);
}

?>
