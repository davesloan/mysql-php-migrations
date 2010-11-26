<?php

$db_config = (object) array();
$db_config->host = 'localhost';
$db_config->port = '3306';
$db_config->user = 'root';
$db_config->pass = 'almond';
$db_config->name = 'mpm_test';
$db_config->db_path = '/home/dave/Projects/mysql-php-migrations/db/';
$db_config->method = 2;
$db_config->migrations_table = 'mpm_migrations';

?>