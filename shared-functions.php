<?php
/**
 * shared-functions.php
 * Functions shared between the writer app (root) and the admin app (sudo/).
 * Included by both functions.php and sudo/functions.php.
 */

require_once __DIR__ . '/config.php';

use PHPMailer\PHPMailer\PHPMailer;

// ---------------------------------------------------------------------------
// Mail
// ---------------------------------------------------------------------------

function configureMail(PHPMailer $mail): void {
    $mail->isSMTP();
    $mail->Host       = env('MAIL_HOST');
    $mail->SMTPAuth   = true;
    $mail->Username   = env('MAIL_USERNAME');
    $mail->Password   = env('MAIL_PASSWORD');
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = (int) env('MAIL_PORT', '587');
}

// ---------------------------------------------------------------------------
// CSRF
// ---------------------------------------------------------------------------

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}

function validate_csrf(): void {
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        exit('Invalid CSRF token.');
    }
}

// ---------------------------------------------------------------------------
// Session/alert helpers
// ---------------------------------------------------------------------------

function set_message($message): void {
    if (!empty($message)) {
        $_SESSION['message'] = $message;
    }
}

function display_message(): void {
    if (!empty($_SESSION['message'])) {
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
        <p class="mb-0 flex-1"><strong>Error: </strong>' . $_SESSION['message'] . '</p>
        <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>';
        unset($_SESSION['message']);
    }
}

function set_alert($alert): void {
    if (!empty($alert)) {
        $_SESSION['alert'] = $alert;
    }
}

function display_alert(): void {
    if (isset($_SESSION['alert'])) {
        echo $_SESSION['alert'];
        unset($_SESSION['alert']);
    }
}

function set_subAlert($subAlert): void {
    if (!empty($subAlert)) {
        $_SESSION['subAlert'] = $subAlert;
    }
}

function display_subAlert(): void {
    if (isset($_SESSION['subAlert'])) {
        echo $_SESSION['subAlert'];
        unset($_SESSION['subAlert']);
    }
}

// ---------------------------------------------------------------------------
// Navigation / validation
// ---------------------------------------------------------------------------

function redirect(string $location): void {
    header("Location: {$location}");
    exit();
}

function validation_errors(string $error_message): void {
    $html = '<div class="alert alert-danger text-center" role="alert">
        <strong>Warning!</strong> ' . htmlspecialchars($error_message) . '
    </div>';
    set_message($html);
}

function logged_in(): bool {
    return isset($_SESSION['userSession']) || isset($_COOKIE['email']);
}

// ---------------------------------------------------------------------------
// Time / formatting
// ---------------------------------------------------------------------------

function timeAgo(string $datetime): string {
    $interval = (new DateTime())->diff(new DateTime($datetime));

    if ($interval->y > 0) return $interval->y . ' year'   . ($interval->y > 1 ? 's' : '') . ' ago';
    if ($interval->m > 0) return $interval->m . ' month'  . ($interval->m > 1 ? 's' : '') . ' ago';
    if ($interval->d > 0) return $interval->d . ' day'    . ($interval->d > 1 ? 's' : '') . ' ago';
    if ($interval->h > 0) return $interval->h . ' hour'   . ($interval->h > 1 ? 's' : '') . ' ago';
    if ($interval->i > 0) return $interval->i . ' minute' . ($interval->i > 1 ? 's' : '') . ' ago';
    return 'Just now';
}

function timeSubAgo(string $datetime, int $showFullDateAfter = 31): string {
    $time = time() - strtotime($datetime);
    $days = floor($time / 86400);

    if ($days > $showFullDateAfter) return date('M j, Y g:i A', strtotime($datetime));
    if ($time < 60)    return 'just now';
    if ($time < 3600)  return floor($time / 60)   . 'm ago';
    if ($time < 86400) return floor($time / 3600)  . 'h ago';
    return $days . 'd ago';
}

function timeDueIn(string $datetime, int $showFullDateAfter = 31, bool $returnArray = false): array|string {
    $dueTime  = strtotime($datetime);
    $timeDiff = $dueTime - time();
    $absDays  = abs(floor($timeDiff / 86400));

    if ($absDays > $showFullDateAfter) {
        $text = date('M j, Y g:i A', $dueTime);
        $cssClass = 'text-muted';
    } elseif ($timeDiff < 0) {
        $abs = abs($timeDiff);
        if ($abs < 3600)      $text = floor($abs / 60)   . 'mins overdue';
        elseif ($abs < 86400) $text = floor($abs / 3600)  . 'hrs overdue';
        else                  $text = $absDays            . 'days overdue';
        $cssClass = 'text-danger';
    } else {
        if ($timeDiff < 3600)       { $text = floor($timeDiff / 60)   . 'mins left';  $cssClass = 'text-danger'; }
        elseif ($timeDiff < 86400)  { $text = floor($timeDiff / 3600)  . 'hrs left';   $cssClass = 'text-warning'; }
        elseif ($absDays <= 3)      { $text = $absDays . 'days left';                  $cssClass = 'text-warning'; }
        else                        { $text = $absDays . 'days left';                  $cssClass = 'text-success'; }
    }

    return $returnArray ? ['text' => $text, 'class' => $cssClass] : $text;
}

function formatSizeUnits(int $bytes): string {
    if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2) . ' GB';
    if ($bytes >= 1048576)    return number_format($bytes / 1048576, 2)    . ' MB';
    if ($bytes >= 1024)       return number_format($bytes / 1024, 2)       . ' KB';
    if ($bytes > 1)           return $bytes . ' bytes';
    if ($bytes === 1)         return '1 byte';
    return '0 bytes';
}

function formatFileSize(?int $bytes): string {
    if ($bytes === null || $bytes === 0) return 'Unknown size';
    $units = ['B', 'KB', 'MB', 'GB'];
    $pow   = min(floor(log($bytes) / log(1024)), count($units) - 1);
    return round($bytes / pow(1024, $pow), 2) . ' ' . $units[$pow];
}

// ---------------------------------------------------------------------------
// File helpers
// ---------------------------------------------------------------------------

function sanitizeFileName(string $fileName): string {
    $fileName = preg_replace('/[^\w\-.]/', '_', $fileName);
    return preg_replace('/_+/', '_', $fileName);
}

function generateSecureFilename(string $extension, string $originalFilename = ''): string {
    $prefix = $originalFilename ? pathinfo($originalFilename, PATHINFO_FILENAME) . '_' : '';
    return $prefix . bin2hex(random_bytes(8)) . '.' . ltrim($extension, '.');
}

function getUploadErrorMessage(int $errorCode): string {
    return match ($errorCode) {
        UPLOAD_ERR_INI_SIZE   => 'File exceeds server upload limit.',
        UPLOAD_ERR_FORM_SIZE  => 'File exceeds form upload limit.',
        UPLOAD_ERR_PARTIAL    => 'File was only partially uploaded.',
        UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder.',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
        UPLOAD_ERR_EXTENSION  => 'Upload blocked by server extension.',
        default               => 'Unknown upload error.',
    };
}

// ---------------------------------------------------------------------------
// JSON responses
// ---------------------------------------------------------------------------

function sendJsonResponse(bool $success, string $message, mixed $data = null): never {
    header('Content-Type: application/json');
    $payload = ['success' => $success, 'message' => $message];
    if ($data !== null) $payload['data'] = $data;
    echo json_encode($payload);
    exit();
}

// ---------------------------------------------------------------------------
// Password validation
// ---------------------------------------------------------------------------

function validatePassword(string $password): array {
    $errors = [];
    if (strlen($password) < 8)              $errors[] = 'at least 8 characters';
    if (!preg_match('/[A-Z]/', $password))  $errors[] = 'an uppercase letter';
    if (!preg_match('/[a-z]/', $password))  $errors[] = 'a lowercase letter';
    if (!preg_match('/[0-9]/', $password))  $errors[] = 'a number';
    if (!preg_match('/[\W_]/', $password))  $errors[] = 'a special character';
    return $errors;
}

// ---------------------------------------------------------------------------
// Misc
// ---------------------------------------------------------------------------

function sanitize(string $data): string {
    return htmlspecialchars(strip_tags(trim($data)));
}

function getPriorityBadge(string $priority): string {
    return match ($priority) {
        'low'    => 'badge-success',
        'medium' => 'badge-warning',
        'high'   => 'badge-danger',
        default  => 'badge-secondary',
    };
}
