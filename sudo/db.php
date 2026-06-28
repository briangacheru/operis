<?php
require_once __DIR__ . '/config.php';

$con = new mysqli(env('DB_HOST'), env('DB_USER'), env('DB_PASS'), env('DB_NAME'));
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}
$con->set_charset("utf8mb4");
