<?php

/**
 * Helpers — common utilities used throughout the writer app.
 *
 * All functions are pure (no globals) unless they explicitly touch $_SESSION.
 */

// =============================================================================
// Flash messages & alerts
// =============================================================================

function setMessage(string $message): void
{
    if ($message !== '') {
        $_SESSION['message'] = $message;
    }
}

function displayMessage(): void
{
    if (!empty($_SESSION['message'])) {
        $msg = htmlspecialchars($_SESSION['message'], ENT_QUOTES, 'UTF-8');
        echo <<<HTML
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <p class="mb-0 flex-1"><strong>Error: </strong>{$msg}</p>
            <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        HTML;
        unset($_SESSION['message']);
    }
}

function setAlert(string $html): void
{
    if ($html !== '') {
        $_SESSION['alert'] = $html;
    }
}

function displayAlert(): void
{
    if (isset($_SESSION['alert'])) {
        echo $_SESSION['alert'];
        unset($_SESSION['alert']);
    }
}

function setSubAlert(string $html): void
{
    if ($html !== '') {
        $_SESSION['subAlert'] = $html;
    }
}

function displaySubAlert(): void
{
    if (isset($_SESSION['subAlert'])) {
        echo $_SESSION['subAlert'];
        unset($_SESSION['subAlert']);
    }
}

/** Render a dismissible Bootstrap danger banner and store it in the session. */
function validationErrors(string $errorMessage): void
{
    $safe = htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8');
    setMessage(
        "<div class=\"alert alert-danger text-center\" role=\"alert\">" .
        "<strong>Warning!</strong> {$safe}</div>"
    );
}

// =============================================================================
// Redirection
// =============================================================================

function redirect(string $location): never
{
    header("Location: {$location}");
    exit();
}

/**
 * Validate and sanitize a redirect URL so it never leaves the current domain.
 * Returns 'index.php' for any external or login.php URL.
 */
function safeRedirectUrl(string $url, string $fallback = 'index.php'): string
{
    if (strpos($url, 'http://') === 0 || strpos($url, 'https://') === 0) {
        $parsed = parse_url($url);
        if (($parsed['host'] ?? '') !== ($_SERVER['HTTP_HOST'] ?? '')) {
            return $fallback;
        }
    }

    if (strpos($url, 'login.php') !== false) {
        return $fallback;
    }

    return $url ?: $fallback;
}

// =============================================================================
// Input sanitization
// =============================================================================

/**
 * Strip tags and encode HTML entities. Use for any user-supplied output.
 */
function sanitize(mixed $value): string
{
    return htmlspecialchars(strip_tags((string) $value), ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitize an array of POST/GET values in one call.
 *
 * @param  array<string>  $keys   Fields to sanitize.
 * @param  array          $source Input array (defaults to $_POST).
 * @return array<string, string>
 */
function sanitizeFields(array $keys, array $source = []): array
{
    if ($source === []) {
        $source = $_POST;
    }
    $out = [];
    foreach ($keys as $key) {
        $out[$key] = isset($source[$key]) ? sanitize($source[$key]) : '';
    }
    return $out;
}

// =============================================================================
// CSRF helpers (thin wrappers — full implementation lives in Auth)
// =============================================================================

/** Emit a hidden CSRF input ready to drop into any form. */
function csrfField(): string
{
    $token = Auth::csrfToken();
    return '<input type="hidden" name="_csrf" value="' . $token . '">';
}

// =============================================================================
// Time utilities
// =============================================================================

/**
 * Human-readable elapsed time, e.g. "3 hours ago".
 */
function timeAgo(string $datetime): string
{
    $commentTime = new DateTime($datetime);
    $now         = new DateTime();
    $interval    = $now->diff($commentTime);

    if ($interval->y > 0) {
        return $interval->y . ' year'   . ($interval->y > 1 ? 's' : '') . ' ago';
    }
    if ($interval->m > 0) {
        return $interval->m . ' month'  . ($interval->m > 1 ? 's' : '') . ' ago';
    }
    if ($interval->d > 0) {
        return $interval->d . ' day'    . ($interval->d > 1 ? 's' : '') . ' ago';
    }
    if ($interval->h > 0) {
        return $interval->h . ' hour'   . ($interval->h > 1 ? 's' : '') . ' ago';
    }
    if ($interval->i > 0) {
        return $interval->i . ' minute' . ($interval->i > 1 ? 's' : '') . ' ago';
    }
    return 'Just now';
}

/**
 * Deadline countdown / overdue text with Bootstrap colour class.
 *
 * @param  string $datetime         MySQL-compatible datetime string.
 * @param  int    $showFullDateAfter Show absolute date for deadlines further
 *                                  than this many days away.
 * @param  bool   $returnArray      When true return ['text'=>…, 'class'=>…].
 * @return string|array<string,string>
 */
function timeDueIn(string $datetime, int $showFullDateAfter = 31, bool $returnArray = false): string|array
{
    $currentTime = time();
    $dueTime     = strtotime($datetime);
    $timeDiff    = $dueTime - $currentTime;
    $absDays     = abs((int) floor($timeDiff / 86400));

    if ($absDays > $showFullDateAfter) {
        $text     = date('M j, Y g:i A', $dueTime);
        $cssClass = 'text-muted';
    } elseif ($timeDiff < 0) {
        $absTime = abs($timeDiff);
        if ($absTime < 3600)       $text = floor($absTime / 60)   . 'mins overdue';
        elseif ($absTime < 86400)  $text = floor($absTime / 3600) . 'hrs overdue';
        else                       $text = $absDays               . 'days overdue';
        $cssClass = 'text-danger';
    } else {
        if ($timeDiff < 3600) {
            $text     = floor($timeDiff / 60)   . 'mins left';
            $cssClass = 'text-danger';
        } elseif ($timeDiff < 86400) {
            $text     = floor($timeDiff / 3600) . 'hrs left';
            $cssClass = 'text-warning';
        } elseif ($absDays <= 3) {
            $text     = $absDays . 'days left';
            $cssClass = 'text-warning';
        } else {
            $text     = $absDays . 'days left';
            $cssClass = 'text-success';
        }
    }

    return $returnArray ? ['text' => $text, 'class' => $cssClass] : $text;
}

// =============================================================================
// File utilities
// =============================================================================

function formatSizeUnits(int $bytes): string
{
    if ($bytes >= 1_073_741_824) return number_format($bytes / 1_073_741_824, 2) . ' GB';
    if ($bytes >= 1_048_576)     return number_format($bytes / 1_048_576,     2) . ' MB';
    if ($bytes >= 1_024)         return number_format($bytes / 1_024,         2) . ' KB';
    if ($bytes > 1)              return $bytes . ' bytes';
    if ($bytes === 1)            return '1 byte';
    return '0 bytes';
}

// =============================================================================
// Text utilities
// =============================================================================

/**
 * Truncate a string to $length characters, appending $suffix if truncated.
 */
function truncate(string $text, int $length = 100, string $suffix = '…'): string
{
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    return mb_substr($text, 0, $length) . $suffix;
}

/**
 * Convert a snake_case or kebab-case string to Title Case.
 */
function toTitleCase(string $value): string
{
    return ucwords(str_replace(['-', '_'], ' ', $value));
}

// =============================================================================
// Request utilities
// =============================================================================

function isPost(): bool
{
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

function isGet(): bool
{
    return $_SERVER['REQUEST_METHOD'] === 'GET';
}

function isAjax(): bool
{
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
        && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Return JSON and exit. Convenience wrapper for AJAX endpoints.
 *
 * @param  mixed $data
 * @param  int   $status  HTTP status code.
 */
function jsonResponse(mixed $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_THROW_ON_ERROR);
    exit();
}

// =============================================================================
// Version helpers
// =============================================================================

function getVersionNumber(): string
{
    $file = __DIR__ . '/../sudo/version.json';
    if (!file_exists($file)) {
        return 'v3.0.0';
    }
    $data = json_decode(file_get_contents($file), true);
    if (json_last_error() !== JSON_ERROR_NONE || !isset($data['major'])) {
        return 'v3.0.0';
    }
    return "v{$data['major']}.{$data['minor']}.{$data['patch']}";
}

function getVersionDescription(): string
{
    $file = __DIR__ . '/../sudo/version.json';
    if (!file_exists($file)) return '';
    $data = json_decode(file_get_contents($file), true);
    return (json_last_error() === JSON_ERROR_NONE) ? ($data['description'] ?? '') : '';
}

function getVersionLastUpdated(string $format = 'F j, Y'): string
{
    $file = __DIR__ . '/../sudo/version.json';
    if (!file_exists($file)) return date($format);
    $data = json_decode(file_get_contents($file), true);
    if (json_last_error() !== JSON_ERROR_NONE || !isset($data['lastUpdated'])) {
        return date($format);
    }
    return date($format, strtotime($data['lastUpdated']));
}

// =============================================================================
// Legacy shim aliases
// =============================================================================

// These one-liners allow old files that call the old camelCase names to keep
// working without modification while new code uses the canonical names above.

function set_message(string $m): void       { setMessage($m); }
function display_message(): void            { displayMessage(); }
function set_alert(string $h): void         { setAlert($h); }
function display_alert(): void              { displayAlert(); }
function set_subAlert(string $h): void      { setSubAlert($h); }
function display_subAlert(): void           { displaySubAlert(); }
function validation_errors(string $e): void { validationErrors($e); }
function logged_in(): bool                  { return Auth::isLoggedIn(); }
function check_login(): void                { Auth::requireLogin(); }
function updateUserStatus(string $email, bool $online): void
{
    Auth::updateOnlineStatus($email, $online);
}
