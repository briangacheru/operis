<?php
ob_start();
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('Africa/Nairobi');
include('dbcon.php');
function check_login()
{
if (!isset($_SESSION['sessionWriter']) || strlen($_SESSION['sessionWriter']) == 0)
    {
        $host = $_SERVER['HTTP_HOST'];
        $uri  = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
        $extra="login.php";
        $_SESSION["id"]="";
        header("Location: http://$host$uri/$extra");
        exit();
    }
}

$_self = $_SERVER["PHP_SELF"];
if(stripos($_self, 'index.php')){
    if(!isset($_SESSION['sessionWriter']) || (isset($_SESSION['sessionWriter']) && $_SESSION['sessionWriter'] <= 0)){
        header('location: login.php');
        exit();
    }
} elseif(stripos($_self, 'login.php') || stripos($_self, 'reset-password.php') || stripos($_self, 'forgot-password.php')){
    if(isset($_SESSION['sessionWriter']) && $_SESSION['sessionWriter'] > 0){
        header('location: index.php');
        exit();
    }
}

// If the "Remember Me" cookie is set, bypass the auto-logout feature
if (!isset($_COOKIE['rememberme'])) {
    // Check if last_activity is set
    if (isset($_SESSION['last_activity'])) {
        // Check if the session is older than 12 hours
        if (time() - $_SESSION['last_activity'] > 43200) {
            // User has been inactive for more than 12 hours
            session_unset();     // unset $_SESSION variables
            session_destroy();   // destroy session data in the server
            setcookie('PHPSESSID', '', time() - 3600, '/'); // destroy session data in the cookie
            header("Location: login.php"); // redirect to logout page or login page
            exit();
        }
    }

    // Update last activity time stamp
    $_SESSION['last_activity'] = time();
}

// If the "Remember Me" cookie is set, log the user in
if (!isset($_SESSION['sessionWriter']) && isset($_COOKIE['rememberme'])) {
    // Look for the user with this remember token
    $rememberToken = $_COOKIE['rememberme'];
    $selectUserSql = "SELECT email FROM tblwriters WHERE remember_token = ?";
    $stmt = $con->prepare($selectUserSql);
    $stmt->bind_param('s', $rememberToken);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $_SESSION['sessionWriter'] = $row['email']; // Log the user in by setting the session variable
    }
}

?>
