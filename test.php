<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once "mysqlProcess.php";

$mp = new MysqlProcess;
// $mp->version1();
// $mp->version1(true);
// $mp->version2(true);
$mp->version3();
