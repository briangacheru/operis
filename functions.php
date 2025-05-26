<?php
function email_exists($email)
{
    global $con;

    $sql = "SELECT id FROM tblwriters WHERE email = '$email'";

    $result = $con->query($sql);

    if($result->num_rows == 1 ) {
        return true;
    } else {
        return false;
    }
}
function username_exists($username)
{
    global $con;

    $sql = "SELECT id FROM tblwriters WHERE username = '$username'";

    $result = $con->query($sql);

    if($result->num_rows == 1 ) {
        return true;
    } else {
        return false;
    }
}

function get_name($email) {
    global $con;

    $sql = "SELECT username FROM tbladmin WHERE email = '$email'";

    $result = $con->query($sql);

    $row = $result->fetch_assoc();

    return $row["username"];
}

function get_email($email) {
    global $con;

    $sql = "SELECT email FROM tbladmin WHERE email = '$email'";

    $result = $con->query($sql);

    $row = $result->fetch_assoc();

    return $row["email"];
}

function get_picture($email) {
    global $con;

    $sql = "SELECT profile_picture FROM tbladmin WHERE email = '$email'";

    $result = $con->query($sql);

    $row = $result->fetch_assoc();

    return $row["profile_picture"];
}


function set_message($message)
{
    if(!empty($message)){
        $_SESSION['message'] = $message;
    }else {
        $message = "";
    }
}

function display_message() {
    if (isset($_SESSION['message']) && !empty($_SESSION['message'])) {
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
        <p class="mb-0 flex-1"><strong>Error: </strong>' . $_SESSION['message'] . '</p>
        <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>';
        unset($_SESSION['message']);
    }
}

function set_alert($alert) {
    if(!empty($alert)) {
        $_SESSION['alert'] = $alert;
    } else {
        $alert = "";
    }
}

function display_alert() {
    if(isset($_SESSION['alert'])) {
        echo $_SESSION['alert'];
        unset($_SESSION['alert']); // Clear the alert after displaying it
    }
}

function set_subAlert($subAlert) {
    if(!empty($subAlert)) {
        $_SESSION['subAlert'] = $subAlert;
    } else {
        $subAlert = "";
    }
}

function display_subAlert() {
    if(isset($_SESSION['subAlert'])) {
        echo $_SESSION['subAlert'];
        unset($_SESSION['subAlert']); // Clear the alert after displaying it
    }
}


function redirect($location){
    return header("Location: {$location}");
}

function validation_errors($error_message)
{
    $error_message = <<<DELIMITER

<div class="alert alert-danger text-center" role="alert">
  	<strong>Warning!</strong> $error_message
 </div>
DELIMITER;

    set_message($error_message);
}

function logged_in(){
    if(isset($_SESSION['userSession']) || isset($_COOKIE['email'])){
        return true;
    } else {
        return false;
    }
}

/**
 * Gets the current version number from the sudo/version.json file
 * @return string The current version number
 */
function getVersionNumber() {
    $versionFile = __DIR__ . '/sudo/version.json';

    // Check if version file exists
    if (!file_exists($versionFile)) {
        return "v1.0.0"; // Default version if file doesn't exist
    }

    // Read current version
    $versionData = json_decode(file_get_contents($versionFile), true);

    // Check if parsing was successful
    if (json_last_error() !== JSON_ERROR_NONE || !isset($versionData['major'])) {
        return "v1.0.0"; // Default version if file is invalid
    }

    // Return formatted version string
    return "v{$versionData['major']}.{$versionData['minor']}.{$versionData['patch']}";
}

?>