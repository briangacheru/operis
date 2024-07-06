<?php
ob_start();
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('Africa/Nairobi');

include('dbcon.php');

function check_login() {
    if (!isset($_SESSION['odmsaid']) || strlen($_SESSION['odmsaid']) == 0) {
        $host = $_SERVER['HTTP_HOST'];
        $uri  = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
        $extra = "login.php";
        $_SESSION["id"] = "";
        header("Location: http://$host$uri/$extra");
        exit();
    }
}

function updateUserStatus($email, $userType, $isOnline) {
    global $con;
    $table = $userType === 'admin' ? 'tbladmin' : 'tblwriters';
    $lastSeen = $isOnline ? 'NOW()' : 'NOW()';

    $query = "UPDATE $table SET is_online = ?, last_seen = $lastSeen WHERE email = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("is", $isOnline, $email);
    $stmt->execute();
    $stmt->close();
}

function logout() {
    if (isset($_SESSION['odmsaid'])) {
        $email = $_SESSION['odmsaid'];
        $userType = 'admin'; // Adjust if necessary
        updateUserStatus($email, $userType, false);
    }
    session_unset();     // Unset $_SESSION variables
    session_destroy();   // Destroy session data on the server
    setcookie('PHPSESSID', '', time() - 3600, '/'); // Destroy session data in the cookie
}

$self = $_SERVER["PHP_SELF"];
$allowed_pages = ['login.php', 'reset-password.php', 'forgot-password.php'];

if (stripos($self, 'index.php') !== false) {
    if (!isset($_SESSION['odmsaid']) || (isset($_SESSION['odmsaid']) && strlen($_SESSION['odmsaid']) == 0)) {
        header('Location: login.php');
        exit();
    }
} elseif (array_reduce($allowed_pages, fn($carry, $page) => $carry || stripos($self, $page) !== false, false)) {
    if (isset($_SESSION['odmsaid']) && strlen($_SESSION['odmsaid']) > 0) {
        header('Location: index.php');
        exit();
    }
}

// Define session timeout duration
$session_timeout_duration = 3600; // 60 minutes

// Check if last_activity is set
if (isset($_SESSION['last_activity'])) {
    // Check if the session is older than 60 minutes
    if (time() - $_SESSION['last_activity'] > $session_timeout_duration) {
        // Store the current page before logging out
        $_SESSION['last_page'] = $_SERVER['REQUEST_URI'];

        // Logout the user
        logout();

        // Redirect to login page
        header("Location: login.php");
        exit();
    }
}

// Update last activity time stamp
$_SESSION['last_activity'] = time();

// Update user status to online
if (isset($_SESSION['odmsaid'])) {
    $email = $_SESSION['odmsaid'];
    $userType = 'admin'; // Adjust if necessary
    updateUserStatus($email, $userType, true);
}

// If the "Remember Me" cookie is set, log the user in
if (!isset($_SESSION['odmsaid']) && isset($_COOKIE['rememberme'])) {
    // Look for the user with this remember token
    $rememberToken = $_COOKIE['rememberme'];
    $selectUserSql = "SELECT email FROM tbladmin WHERE remember_token = ?";
    $stmt = $con->prepare($selectUserSql);
    $stmt->bind_param('s', $rememberToken);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $_SESSION['odmsaid'] = $row['email']; // Log the user in by setting the session variable

        // Update user status to online
        $email = $_SESSION['odmsaid'];
        $userType = 'admin'; // Adjust if necessary
        updateUserStatus($email, $userType, true);

        // Redirect back to the last page if set
        if (isset($_SESSION['last_page'])) {
            $last_page = $_SESSION['last_page'];
            unset($_SESSION['last_page']); // Clear the stored last page
            header("Location: $last_page");
            exit();
        }
    }
}
?>
