<?php
require_once __DIR__ . '/../shared-functions.php';
require_once __DIR__ . '/../version-functions.php';

// ---------------------------------------------------------------------------
// Admin-specific DB queries  (table: tbladmin)
// ---------------------------------------------------------------------------

function email_exists(string $email): bool {
    global $con;
    $stmt = $con->prepare("SELECT id FROM tbladmin WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();
    return $stmt->num_rows === 1;
}

function username_exists(string $username): bool {
    global $con;
    $stmt = $con->prepare("SELECT id FROM tbladmin WHERE username = ?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $stmt->store_result();
    return $stmt->num_rows === 1;
}

function get_name(string $email): ?string {
    global $con;
    $stmt = $con->prepare("SELECT username FROM tbladmin WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['username'] ?? null;
}

function get_email(string $email): ?string {
    global $con;
    $stmt = $con->prepare("SELECT email FROM tbladmin WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['email'] ?? null;
}

function get_picture(string $email): ?string {
    global $con;
    $stmt = $con->prepare("SELECT profile_picture FROM tbladmin WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['profile_picture'] ?? null;
}

// ---------------------------------------------------------------------------
// Admin session / auth
// ---------------------------------------------------------------------------

function updateUserStatus(string $email, string $userType, bool $isOnline): void {
    global $con;
    $table = ($userType === 'admin') ? 'tbladmin' : 'tblwriters';
    $stmt  = $con->prepare("UPDATE $table SET is_online = ?, last_seen = NOW() WHERE email = ?");
    $stmt->bind_param('is', $isOnline, $email);
    $stmt->execute();
}

function logout(): void {
    if (isset($_SESSION['userSession'])) {
        updateUserStatus($_SESSION['userSession'], 'admin', false);
    }
    session_unset();
    session_destroy();
    setcookie('PHPSESSID', '', time() - 3600, '/');
}

// ---------------------------------------------------------------------------
// Version management (admin only)
// ---------------------------------------------------------------------------

function updateVersionNumber(string $type = 'patch', string $description = ''): array {
    $file = __DIR__ . '/version.json';
    $data = file_exists($file) ? json_decode(file_get_contents($file), true) : null;

    if (!$data || json_last_error() !== JSON_ERROR_NONE || !isset($data['major'])) {
        $data = ['major' => 3, 'minor' => 0, 'patch' => 0, 'lastUpdated' => date('Y-m-d'), 'description' => 'Initial release'];
    }

    match ($type) {
        'major' => ($data['major']++, $data['minor'] = 0, $data['patch'] = 0),
        'minor' => ($data['minor']++, $data['patch'] = 0),
        default => $data['patch']++,
    };

    $data['lastUpdated'] = date('Y-m-d');
    if ($description) $data['description'] = $description;

    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
    return $data;
}

function getVersionData(): array {
    $file = __DIR__ . '/version.json';
    if (!file_exists($file)) {
        $data = ['major' => 3, 'minor' => 0, 'patch' => 0, 'lastUpdated' => date('Y-m-d'), 'description' => 'Initial release'];
        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
        return $data;
    }
    $data = json_decode(file_get_contents($file), true);
    if (json_last_error() !== JSON_ERROR_NONE || !isset($data['major'])) {
        $data = ['major' => 3, 'minor' => 0, 'patch' => 0, 'lastUpdated' => date('Y-m-d'), 'description' => 'Initial release'];
        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
    }
    return $data;
}
