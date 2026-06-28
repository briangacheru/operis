<?php
require_once __DIR__ . '/config.php';

try {
    $dbh = new PDO(
        "mysql:host=" . env('DB_HOST') . ";dbname=" . env('DB_NAME'),
        env('DB_USER'),
        env('DB_PASS'),
        [PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"]
    );
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    exit("Error: " . $e->getMessage());
}

$con = new mysqli(env('DB_HOST'), env('DB_USER'), env('DB_PASS'), env('DB_NAME'));
if (mysqli_connect_errno()) {
    exit("Connection failed: " . mysqli_connect_error());
}
$con->set_charset("utf8mb4");
