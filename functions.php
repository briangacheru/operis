<?php
require_once __DIR__ . '/shared-functions.php';
require_once __DIR__ . '/includes/Database.php';

// ---------------------------------------------------------------------------
// Writer-specific DB queries  (table: tblwriters)
// ---------------------------------------------------------------------------

function email_exists(string $email): bool {
    $con  = Database::getMySQLi();
    $stmt = $con->prepare("SELECT id FROM tblwriters WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();
    return $stmt->num_rows === 1;
}

function username_exists(string $username): bool {
    $con  = Database::getMySQLi();
    $stmt = $con->prepare("SELECT id FROM tblwriters WHERE username = ?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $stmt->store_result();
    return $stmt->num_rows === 1;
}

// ---------------------------------------------------------------------------
// Admin profile helpers  (table: tbladmin) — used by writer app for display
// ---------------------------------------------------------------------------

function get_name(string $email): ?string {
    $con  = Database::getMySQLi();
    $stmt = $con->prepare("SELECT username FROM tbladmin WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['username'] ?? null;
}

function get_email(string $email): ?string {
    $con  = Database::getMySQLi();
    $stmt = $con->prepare("SELECT email FROM tbladmin WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['email'] ?? null;
}

function get_picture(string $email): ?string {
    $con  = Database::getMySQLi();
    $stmt = $con->prepare("SELECT profile_picture FROM tbladmin WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['profile_picture'] ?? null;
}

// ---------------------------------------------------------------------------
// Writer session / auth
// ---------------------------------------------------------------------------

function check_login(): void {
    if (empty($_SESSION['sessionWriter'])) {
        $_SESSION['id'] = '';
        $host = $_SERVER['HTTP_HOST'];
        $uri  = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
        header("Location: http://$host$uri/login.php");
        exit();
    }
}

/**
 * Update online status for a writer or admin.
 * Table name is resolved from a fixed allowlist — never interpolated from user input.
 */
function updateUserStatus(string $email, string $userType, bool $isOnline): void {
    $allowed = ['admin' => 'tbladmin', 'writer' => 'tblwriters'];
    if (!isset($allowed[$userType])) {
        throw new InvalidArgumentException("Unknown user type: $userType");
    }
    $table = $allowed[$userType];   // safe: resolved from allowlist, not user input
    $con   = Database::getMySQLi();
    $stmt  = $con->prepare("UPDATE `$table` SET is_online = ?, last_seen = NOW() WHERE email = ?");
    $stmt->bind_param('is', $isOnline, $email);
    $stmt->execute();
}

function logout(): void {
    if (isset($_SESSION['sessionWriter'])) {
        updateUserStatus($_SESSION['sessionWriter'], 'writer', false);
        $lastPage = $_SESSION['last_page'] ?? $_SERVER['REQUEST_URI'] ?? '';
        if ($lastPage) {
            setcookie('last_page_before_logout', $lastPage, time() + 300, '/', '', isset($_SERVER['HTTPS']), true);
        }
    }
    session_unset();
    session_destroy();
    setcookie('PHPSESSID', '', time() - 3600, '/');
}

function getVersionNumber(): string {
    $file = __DIR__ . '/sudo/version.json';
    if (!file_exists($file)) return 'v1.0.0';
    $data = json_decode(file_get_contents($file), true);
    if (json_last_error() !== JSON_ERROR_NONE || !isset($data['major'])) return 'v1.0.0';
    return "v{$data['major']}.{$data['minor']}.{$data['patch']}";
}
