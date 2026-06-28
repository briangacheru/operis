<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/Database.php';

$con = new mysqli(env('DB_HOST'), env('DB_USER'), env('DB_PASS'), env('DB_NAME'));
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}
$con->set_charset("utf8mb4");

// Register with the singleton so Repositories and Services reuse this socket.
Database::setMySQLi($con);
