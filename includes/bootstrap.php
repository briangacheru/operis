<?php

/**
 * bootstrap.php — application initialisation.
 *
 * Include this single file at the top of every writer-app page instead of
 * the scattered require/include calls that previously appeared everywhere.
 *
 *   require_once __DIR__ . '/includes/bootstrap.php';
 *
 * After this file runs, the following are available globally:
 *   $con  — MySQLi connection (backward-compat alias for Database::getMysqli())
 *   $dbh  — PDO connection    (backward-compat alias for Database::getPdo())
 *   All functions from Helpers.php
 *   Auth class
 *   Database class
 */

// ---------------------------------------------------------------------------
// 1. Output buffering — must be first so header() calls never fail.
// ---------------------------------------------------------------------------
if (ob_get_level() === 0) {
    ob_start();
}

// ---------------------------------------------------------------------------
// 2. Session — start once, regenerate ID on login (handled by Auth::login).
// ---------------------------------------------------------------------------
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// ---------------------------------------------------------------------------
// 3. Timezone
// ---------------------------------------------------------------------------
date_default_timezone_set('Africa/Nairobi');

// ---------------------------------------------------------------------------
// 4. Error handling
//    In production set ENVIRONMENT to 'production' in your web-server config
//    or a .env file so errors are logged instead of displayed.
// ---------------------------------------------------------------------------
$_env = getenv('ENVIRONMENT') ?: 'development';

if ($_env === 'production') {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(0);
    ini_set('log_errors', '1');
    ini_set('error_log', __DIR__ . '/../php-errors.log');
} else {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
    ini_set('log_errors', '1');
    ini_set('error_log', __DIR__ . '/../php-errors.log');
}

// ---------------------------------------------------------------------------
// 5. Database credentials — define before loading Database.php.
// ---------------------------------------------------------------------------
if (!defined('DB_HOST')) {
    define('DB_HOST',    'localhost');
    define('DB_USER',    'root');
    define('DB_PASS',    '');
    define('DB_NAME',    'tasker');
    define('DB_CHARSET', 'utf8mb4');
}

// ---------------------------------------------------------------------------
// 6. Autoload core classes and helpers.
// ---------------------------------------------------------------------------
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Auth.php';
require_once __DIR__ . '/Helpers.php';

// ---------------------------------------------------------------------------
// 7. Backward-compatible globals so existing files don't break.
//    New code should call Database::getInstance()->getMysqli() directly.
// ---------------------------------------------------------------------------
$db  = Database::getInstance();
$con = $db->getMysqli();   // MySQLi — used throughout the writer app
$dbh = $db->getPdo();      // PDO    — used in the admin (sudo/) app

// ---------------------------------------------------------------------------
// 8. Session tracker (records device / IP at login time).
// ---------------------------------------------------------------------------
if (file_exists(__DIR__ . '/../session_tracker.php')) {
    require_once __DIR__ . '/../session_tracker.php';
}
