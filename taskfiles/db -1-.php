<?php
$servername = "localhost";
$username = "monkbria_itasker";
$password = "Av3nt@d0r";
$dbname = "monkbria_itaskerdb";

// Create connection
$con = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}
?>