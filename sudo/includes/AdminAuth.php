<?php

/**
 * AdminAuth — authentication for the admin (sudo/) app.
 *
 * Mirrors the writer-app Auth class but uses the admin session key
 * ('odmsaid'), the admin table ('tbladmin'), and PDO sessions via
 * session_tracker.php.
 */
class AdminAuth
{
    public  const SESSION_KEY        = 'odmsaid';
    private const LAST_ACTIVITY_KEY  = 'last_activity';
    private const SESSION_TIMEOUT    = 86400; // 24 hours
    private const REMEMBER_COOKIE    = 'rememberme';
    private const REMEMBER_TTL       = 86400;

    // -------------------------------------------------------------------------
    // Login / logout
    // -------------------------------------------------------------------------

    /**
     * Authenticate an admin user. Returns true on success (session set).
     * On failure sets $error and returns false.
     */
    public static function login(
        string $email,
        string $password,
        bool   $remember,
        string &$error = ''
    ): bool {
        $db  = Database::getInstance();
        $row = $db->fetchOne(
            "SELECT email, password FROM tbladmin WHERE email = ?",
            "s", $email
        );

        if (!$row || !password_verify($password, $row['password'])) {
            $error = 'Incorrect email or password.';
            return false;
        }

        session_regenerate_id(true);
        $_SESSION[self::SESSION_KEY]       = $email;
        $_SESSION[self::LAST_ACTIVITY_KEY] = time();

        self::updateOnlineStatus($email, true);

        if ($remember) {
            self::setRememberToken($email);
        }

        return true;
    }

    /**
     * Log out the current admin user.
     */
    public static function logout(): void
    {
        if (isset($_SESSION[self::SESSION_KEY])) {
            $email = $_SESSION[self::SESSION_KEY];
            self::updateOnlineStatus($email, false);
        }

        self::clearRememberToken();

        session_unset();
        session_destroy();
        self::secureCookie('PHPSESSID', '', time() - 3600);
    }

    // -------------------------------------------------------------------------
    // Session state
    // -------------------------------------------------------------------------

    public static function isLoggedIn(): bool
    {
        return isset($_SESSION[self::SESSION_KEY])
            && strlen($_SESSION[self::SESSION_KEY]) > 0;
    }

    public static function currentUser(): ?string
    {
        return $_SESSION[self::SESSION_KEY] ?? null;
    }

    /**
     * Enforce admin authentication. Redirects to login on failure.
     * Also handles session timeout and remember-me auto-login.
     *
     * @param string $loginUrl  Relative URL to the admin login page.
     */
    public static function requireLogin(string $loginUrl = 'login'): void
    {
        if (!self::isLoggedIn()) {
            self::tryRememberLogin();
        }

        if (!self::isLoggedIn()) {
            $redirect = urlencode($_SERVER['REQUEST_URI'] ?? '');
            header("Location: {$loginUrl}?redirect={$redirect}");
            exit();
        }

        if (self::isTimedOut()) {
            $lastPage   = $_SESSION['last_page'] ?? '';
            $redirectUrl = urlencode($lastPage);
            self::logout();
            header("Location: {$loginUrl}?redirect={$redirectUrl}");
            exit();
        }

        $_SESSION[self::LAST_ACTIVITY_KEY] = time();
        self::updateOnlineStatus($_SESSION[self::SESSION_KEY], true);
    }

    /**
     * Redirect already-authenticated admins away from guest-only pages.
     */
    public static function redirectIfLoggedIn(string $destination = 'index'): void
    {
        if (self::isLoggedIn()) {
            header("Location: {$destination}");
            exit();
        }
    }

    // -------------------------------------------------------------------------
    // CSRF
    // -------------------------------------------------------------------------

    public static function csrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function verifyCsrf(string $submitted): bool
    {
        return hash_equals($_SESSION['csrf_token'] ?? '', $submitted);
    }

    public static function requireCsrf(string $field = '_csrf'): void
    {
        if (!self::verifyCsrf($_POST[$field] ?? '')) {
            http_response_code(403);
            exit('Invalid or missing CSRF token.');
        }
    }

    // -------------------------------------------------------------------------
    // Online-status tracking (admin table)
    // -------------------------------------------------------------------------

    public static function updateOnlineStatus(string $email, bool $online): void
    {
        $db     = Database::getInstance();
        $status = $online ? 1 : 0;
        $db->query(
            "UPDATE tbladmin SET is_online = ?, last_seen = NOW() WHERE email = ?",
            "is", $status, $email
        );
    }

    // -------------------------------------------------------------------------
    // Remember-me
    // -------------------------------------------------------------------------

    private static function setRememberToken(string $email): void
    {
        $token       = bin2hex(random_bytes(32));
        $hashedToken = password_hash($token, PASSWORD_DEFAULT);

        $db = Database::getInstance();
        $db->query(
            "UPDATE tbladmin SET remember_token = ? WHERE email = ?",
            "ss", $hashedToken, $email
        );

        self::secureCookie(self::REMEMBER_COOKIE, $token, time() + self::REMEMBER_TTL);
    }

    private static function clearRememberToken(): void
    {
        if (isset($_COOKIE[self::REMEMBER_COOKIE]) && isset($_SESSION[self::SESSION_KEY])) {
            $db    = Database::getInstance();
            $email = $_SESSION[self::SESSION_KEY];
            $db->query(
                "UPDATE tbladmin SET remember_token = NULL WHERE email = ?",
                "s", $email
            );
        }
        self::secureCookie(self::REMEMBER_COOKIE, '', time() - 3600);
    }

    private static function tryRememberLogin(): void
    {
        if (!isset($_COOKIE[self::REMEMBER_COOKIE])) {
            return;
        }

        $token = $_COOKIE[self::REMEMBER_COOKIE];
        $db    = Database::getInstance();
        $rows  = $db->fetchAll(
            "SELECT email, remember_token FROM tbladmin WHERE remember_token IS NOT NULL"
        );

        foreach ($rows as $row) {
            if (password_verify($token, $row['remember_token'])) {
                session_regenerate_id(true);
                $_SESSION[self::SESSION_KEY]       = $row['email'];
                $_SESSION[self::LAST_ACTIVITY_KEY] = time();
                self::updateOnlineStatus($row['email'], true);

                // Record session in admin session table via session_tracker.
                $dbh = Database::getInstance()->getPdo();
                if (function_exists('record_login_session')) {
                    record_login_session($dbh, $row['email']);
                }
                return;
            }
        }

        self::secureCookie(self::REMEMBER_COOKIE, '', time() - 3600);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private static function isTimedOut(): bool
    {
        if (!isset($_SESSION[self::LAST_ACTIVITY_KEY])) {
            return false;
        }
        return (time() - $_SESSION[self::LAST_ACTIVITY_KEY]) > self::SESSION_TIMEOUT;
    }

    private static function secureCookie(string $name, string $value, int $expires): void
    {
        $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        setcookie($name, $value, [
            'expires'  => $expires,
            'path'     => '/',
            'secure'   => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }
}
