<?php

/**
 * Auth — authentication and session management.
 *
 * All database calls go through the Database singleton, so no raw $con
 * globals are needed here.
 *
 * Typical bootstrap order:
 *   require_once __DIR__ . '/includes/bootstrap.php';
 *   Auth::requireLogin();   // redirect to login.php if not authenticated
 */
class Auth
{
    private const SESSION_KEY        = 'sessionWriter';
    private const LAST_ACTIVITY_KEY  = 'last_activity';
    private const SESSION_TIMEOUT    = 86400; // 24 hours in seconds
    private const REMEMBER_COOKIE    = 'rememberme';
    private const REMEMBER_TTL       = 86400; // 1 day
    private const COOKIE_TIMEOUT_KEY = 'last_page_before_timeout';
    private const COOKIE_LOGOUT_KEY  = 'last_page_before_logout';

    // -------------------------------------------------------------------------
    // Login / logout
    // -------------------------------------------------------------------------

    /**
     * Attempt to authenticate a writer with email + password.
     *
     * Returns true on success (session is set). Returns false and sets a
     * human-readable $error string on failure.
     */
    public static function login(string $email, string $password, bool $remember, string &$error = ''): bool
    {
        $db  = Database::getInstance();
        $row = $db->fetchOne(
            "SELECT email, password FROM tblwriters WHERE email = ?",
            "s", $email
        );

        if (!$row || !password_verify($password, $row['password'])) {
            $error = 'Incorrect email or password.';
            return false;
        }

        // Regenerate session ID to prevent session fixation.
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
     * Log out the current writer, clear cookies, destroy the session.
     */
    public static function logout(): void
    {
        if (isset($_SESSION[self::SESSION_KEY])) {
            $email = $_SESSION[self::SESSION_KEY];
            self::updateOnlineStatus($email, false);

            // Remember where the user was so we can redirect after re-login.
            $lastPage = $_SESSION['last_page'] ?? $_SERVER['REQUEST_URI'] ?? 'index.php';
            self::secureCookie(self::COOKIE_LOGOUT_KEY, $lastPage, time() + 300);
        }

        // Clear remember-me token from DB and browser.
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
        return isset($_SESSION[self::SESSION_KEY]) && strlen($_SESSION[self::SESSION_KEY]) > 0;
    }

    /** Returns the currently logged-in writer's email, or null. */
    public static function currentUser(): ?string
    {
        return $_SESSION[self::SESSION_KEY] ?? null;
    }

    /**
     * Enforce authentication. Call at the top of every protected page.
     * Redirects to login.php if the session is not valid.
     */
    public static function requireLogin(string $loginUrl = 'login.php'): void
    {
        // Try remember-me auto-login first.
        if (!self::isLoggedIn()) {
            self::tryRememberLogin();
        }

        if (!self::isLoggedIn()) {
            header("Location: $loginUrl");
            exit();
        }

        // Session timeout check.
        if (self::isTimedOut()) {
            $lastPage = $_SERVER['REQUEST_URI'] ?? '';
            self::secureCookie(self::COOKIE_TIMEOUT_KEY, $lastPage, time() + 300);
            self::logout();
            header("Location: {$loginUrl}?timeout=1");
            exit();
        }

        $_SESSION[self::LAST_ACTIVITY_KEY] = time();
        self::updateOnlineStatus($_SESSION[self::SESSION_KEY], true);
    }

    /**
     * Redirect already-authenticated users away from guest-only pages (login,
     * register, forgot-password, etc.).
     */
    public static function redirectIfLoggedIn(string $destination = 'index.php'): void
    {
        if (self::isLoggedIn()) {
            header("Location: $destination");
            exit();
        }
    }

    // -------------------------------------------------------------------------
    // Password utilities
    // -------------------------------------------------------------------------

    public static function hashPassword(string $plain): string
    {
        return password_hash($plain, PASSWORD_DEFAULT);
    }

    public static function verifyPassword(string $plain, string $hash): bool
    {
        return password_verify($plain, $hash);
    }

    /**
     * True if an existing hash needs to be rehashed (e.g. after a cost bump).
     * Call after a successful login and update the DB if true.
     */
    public static function needsRehash(string $hash): bool
    {
        return password_needs_rehash($hash, PASSWORD_DEFAULT);
    }

    // -------------------------------------------------------------------------
    // CSRF protection
    // -------------------------------------------------------------------------

    /**
     * Generate (or retrieve cached) CSRF token for the current session.
     */
    public static function csrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Verify that the submitted CSRF token matches the session token.
     * Timing-safe comparison prevents timing attacks.
     */
    public static function verifyCsrf(string $submitted): bool
    {
        $token = $_SESSION['csrf_token'] ?? '';
        return hash_equals($token, $submitted);
    }

    /**
     * Validate CSRF from a POST request, aborting with 403 on failure.
     * Call at the top of any state-changing POST handler.
     */
    public static function requireCsrf(string $fieldName = '_csrf'): void
    {
        $submitted = $_POST[$fieldName] ?? '';
        if (!self::verifyCsrf($submitted)) {
            http_response_code(403);
            exit('Invalid or missing CSRF token.');
        }
    }

    // -------------------------------------------------------------------------
    // Online-status tracking
    // -------------------------------------------------------------------------

    public static function updateOnlineStatus(string $email, bool $online): void
    {
        $db     = Database::getInstance();
        $status = $online ? 1 : 0;
        $db->query(
            "UPDATE tblwriters SET is_online = ?, last_seen = NOW() WHERE email = ?",
            "is", $status, $email
        );
    }

    // -------------------------------------------------------------------------
    // Remember-me helpers
    // -------------------------------------------------------------------------

    private static function setRememberToken(string $email): void
    {
        $token       = bin2hex(random_bytes(32));
        $hashedToken = password_hash($token, PASSWORD_DEFAULT);

        $db = Database::getInstance();
        $db->query(
            "UPDATE tblwriters SET remember_token = ? WHERE email = ?",
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
                "UPDATE tblwriters SET remember_token = NULL WHERE email = ?",
                "s", $email
            );
        }
        self::secureCookie(self::REMEMBER_COOKIE, '', time() - 3600);
    }

    /**
     * Restore session from a valid remember-me cookie.
     * Called automatically by requireLogin() before the session check.
     */
    private static function tryRememberLogin(): void
    {
        if (!isset($_COOKIE[self::REMEMBER_COOKIE])) {
            return;
        }

        $token = $_COOKIE[self::REMEMBER_COOKIE];
        $db    = Database::getInstance();

        // Fetch all writers that have a non-null token (small set in practice).
        $rows = $db->fetchAll(
            "SELECT email, remember_token FROM tblwriters WHERE remember_token IS NOT NULL"
        );

        foreach ($rows as $row) {
            if (password_verify($token, $row['remember_token'])) {
                session_regenerate_id(true);
                $_SESSION[self::SESSION_KEY]       = $row['email'];
                $_SESSION[self::LAST_ACTIVITY_KEY] = time();
                self::updateOnlineStatus($row['email'], true);
                return;
            }
        }

        // Invalid/expired token — clear the cookie.
        self::secureCookie(self::REMEMBER_COOKIE, '', time() - 3600);
    }

    // -------------------------------------------------------------------------
    // Internal helpers
    // -------------------------------------------------------------------------

    private static function isTimedOut(): bool
    {
        if (!isset($_SESSION[self::LAST_ACTIVITY_KEY])) {
            return false;
        }
        return (time() - $_SESSION[self::LAST_ACTIVITY_KEY]) > self::SESSION_TIMEOUT;
    }

    /**
     * Set a cookie with secure defaults (HttpOnly, SameSite=Lax, Secure on HTTPS).
     */
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
