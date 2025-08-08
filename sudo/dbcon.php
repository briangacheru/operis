<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'tasker');
// Establish database connection
try {
    $dbh = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
        DB_USER,
        DB_PASS,
        array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'")
    );
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    exit("Error: " . $e->getMessage());
}

// Create MySQLi connection
$con = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (mysqli_connect_errno()) {
    echo "Connection Fail: " . mysqli_connect_error();
}

// Ensure connections are closed at the end of the script
register_shutdown_function(function () use (&$dbh, &$con) {
    if ($dbh !== null) {
        $dbh = null; // Close PDO connection
    }
    if ($con !== null) {
        mysqli_close($con); // Close MySQLi connection
    }
});
?>
