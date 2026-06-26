<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'tasker');

$con = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$con->set_charset('utf8');

if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}
?>