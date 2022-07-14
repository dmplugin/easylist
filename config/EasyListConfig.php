<?php

/*
* Configure DB
* Put it in app/config/EasyListConfig.php
*/

$db = array(
    'host'     => '',
    'username' => '',
    'password' => '',
    'database' => '',
    'connection_string' => '',
    'protocol' => 'MYSQL|SQLSRV|ORACLE|POSTGRESQL|SYBASE|INFORMIX',
);

/*
 * This can be edited to change the configuration location
 */
$configPath = dirname(__FILE__, 5).'/app/config/EasyListConfig.php';

