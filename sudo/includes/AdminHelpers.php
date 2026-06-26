<?php

/**
 * AdminHelpers — admin-specific utility functions.
 *
 * Functions only needed in the sudo/ app. Generic utilities (timeAgo,
 * sanitize, formatSizeUnits, etc.) are inherited from includes/Helpers.php
 * which is loaded via sudo/includes/bootstrap.php.
 */

// =============================================================================
// Admin DB lookup helpers (all use prepared statements)
// =============================================================================

function email_exists(string $email): bool
{
    $db = Database::getInstance();
    $row = $db->fetchOne("SELECT id FROM tbladmin WHERE email = ?", "s", $email);
    return $row !== null;
}

function username_exists(string $username): bool
{
    $db = Database::getInstance();
    $row = $db->fetchOne("SELECT id FROM tbladmin WHERE username = ?", "s", $username);
    return $row !== null;
}

function get_name(string $email): ?string
{
    $db  = Database::getInstance();
    $row = $db->fetchOne("SELECT username FROM tbladmin WHERE email = ?", "s", $email);
    return $row['username'] ?? null;
}

function get_email(string $email): ?string
{
    $db  = Database::getInstance();
    $row = $db->fetchOne("SELECT email FROM tbladmin WHERE email = ?", "s", $email);
    return $row['email'] ?? null;
}

function get_picture(string $email): ?string
{
    $db  = Database::getInstance();
    $row = $db->fetchOne("SELECT profile_picture FROM tbladmin WHERE email = ?", "s", $email);
    return $row['profile_picture'] ?? null;
}

// =============================================================================
// Version management (admin-only — modifies sudo/version.json)
// =============================================================================

function updateVersionNumber(string $type = 'patch', string $description = ''): array
{
    $file = __DIR__ . '/../version.json';

    $data = file_exists($file)
        ? (json_decode(file_get_contents($file), true) ?: [])
        : [];

    if (!isset($data['major'])) {
        $data = ['major' => 3, 'minor' => 0, 'patch' => 0];
    }

    match ($type) {
        'major' => [$data['major']++, $data['minor'] = 0, $data['patch'] = 0],
        'minor' => [$data['minor']++, $data['patch'] = 0],
        default => $data['patch']++,
    };

    $data['lastUpdated'] = date('Y-m-d');
    if ($description !== '') {
        $data['description'] = $description;
    }

    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
    return $data;
}

function getVersionData(): array
{
    $file = __DIR__ . '/../version.json';
    if (!file_exists($file)) {
        return ['major' => 3, 'minor' => 0, 'patch' => 0, 'lastUpdated' => date('Y-m-d'), 'description' => ''];
    }
    $data = json_decode(file_get_contents($file), true);
    return (json_last_error() === JSON_ERROR_NONE && isset($data['major'])) ? $data : ['major' => 3, 'minor' => 0, 'patch' => 0];
}

// =============================================================================
// Admin display helpers
// =============================================================================

function getPriorityBadge(string $priority): string
{
    return match (strtolower($priority)) {
        'low'    => 'badge-success',
        'medium' => 'badge-warning',
        'high'   => 'badge-danger',
        default  => 'badge-secondary',
    };
}

function formatDateTime(string $date, string $time): string
{
    return date('M j, Y g:i A', strtotime("{$date} {$time}"));
}

/**
 * Compact time-ago for table cells, e.g. "3h ago", "5d ago".
 */
function timeSubAgo(string $datetime, int $showFullDateAfter = 31): string
{
    $elapsed = time() - strtotime($datetime);
    $days    = (int) floor($elapsed / 86400);

    if ($days > $showFullDateAfter) {
        return date('M j, Y g:i A', strtotime($datetime));
    }
    if ($elapsed < 60)   return 'just now';
    if ($elapsed < 3600) return floor($elapsed / 60)   . 'm ago';
    if ($elapsed < 86400) return floor($elapsed / 3600) . 'h ago';
    return $days . 'd ago';
}

// =============================================================================
// Legacy shim aliases so old sudo/ files keep working without changes
// =============================================================================

function check_login(): void                              { AdminAuth::requireLogin(); }
function logged_in(): bool                                { return AdminAuth::isLoggedIn(); }
function updateUserStatus(string $e, string $t, bool $o): void { AdminAuth::updateOnlineStatus($e, $o); }
function logout(): void                                   { AdminAuth::logout(); }

// set_message / display_message etc. are already provided by Helpers.php shims.
