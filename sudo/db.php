<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "tasker";

// Create connection
$con = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}
mysqli_set_charset($con, "utf8mb4");

?>