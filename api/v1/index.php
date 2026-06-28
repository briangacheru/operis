<?php
declare(strict_types=1);

/**
 * REST API entry point — /api/v1/
 *
 * Route format:  /api/v1/{resource}[/{id}]
 * All responses: JSON, UTF-8.
 *
 * Auth: session-based (same PHPSESSID cookie as the web app).
 * All endpoints require an authenticated session unless noted.
 */

// Allow cross-origin requests from the same host during development.
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

$root = dirname(__DIR__, 2);
require_once $root . '/config.php';
require_once $root . '/db.php';                        // sets $con
require_once $root . '/includes/Database.php';
Database::setMySQLi($con);                             // share existing socket

session_start();

// -----------------------------------------------------------------------
// Router
// -----------------------------------------------------------------------

$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$base   = '/api/v1';
$path   = ltrim(substr($uri, strlen($base)), '/');
$parts  = explode('/', $path);
$resource = $parts[0] ?? '';
$id       = isset($parts[1]) && ctype_digit($parts[1]) ? (int) $parts[1] : null;
$method   = $_SERVER['REQUEST_METHOD'];

// Parse JSON body for POST/PUT/PATCH
$body = [];
if (in_array($method, ['POST', 'PUT', 'PATCH'], true)) {
    $raw  = file_get_contents('php://input');
    $body = $raw ? (json_decode($raw, true) ?? $_POST) : $_POST;
}

function apiResponse(mixed $data, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function requireAuth(): string
{
    if (empty($_SESSION['sessionWriter'])) {
        apiResponse(['error' => 'Unauthenticated'], 401);
    }
    return $_SESSION['sessionWriter'];
}

// -----------------------------------------------------------------------
// Dispatch
// -----------------------------------------------------------------------

match ($resource) {
    'tasks'    => require __DIR__ . '/tasks.php',
    'auth'     => require __DIR__ . '/auth.php',
    'messages' => require __DIR__ . '/messages.php',
    'profile'  => require __DIR__ . '/profile.php',
    'health'   => apiResponse(['status' => 'ok', 'version' => '1']),
    default    => apiResponse(['error' => "Unknown resource: $resource"], 404),
};
