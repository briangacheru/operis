# Refactoring Guide

This document describes the structural changes introduced in this refactor, how to migrate existing pages, and the security improvements made.

---

## Overview of changes

| Before | After |
|--------|-------|
| DB credentials, connections, and helpers scattered across `dbcon.php`, `db.php`, and raw globals | Single `includes/Database.php` singleton, backward-compat `$con`/`$dbh` globals |
| Authentication logic duplicated across `check-login.php`, `login.php`, `logout.php`, `functions.php` | Consolidated in `includes/Auth.php` |
| Utility functions (`timeAgo`, `formatSizeUnits`, alert helpers…) in `functions.php` with no clear organisation | `includes/Helpers.php` with grouped, documented functions plus legacy shim aliases |
| Each page required 4-6 `require`/`include` lines in an inconsistent order | Single `require_once __DIR__ . '/includes/bootstrap.php'` |
| SQL injection vulnerabilities via string interpolation in `functions.php` | All DB access uses MySQLi prepared statements via `Database::query/fetchOne/fetchAll` |
| `updateUserStatus` had a dead `$userType` parameter; both ternary branches were identical | Simplified to `Auth::updateOnlineStatus(string $email, bool $online)` |

---

## New file structure

```
includes/
  bootstrap.php   — application entry point; require this once per page
  Database.php    — singleton DB manager (MySQLi + PDO, query helpers, transactions)
  Auth.php        — login, logout, session timeout, remember-me, CSRF, password hashing
  Helpers.php     — time formatting, alerts, sanitization, redirect, request utils
REFACTORING.md    — this file
```

---

## Migration guide

### Minimal change (keep existing files working)

Replace every page's header block:

```php
// Before (varied across files):
ob_start();
session_start();
date_default_timezone_set('Africa/Nairobi');
include('dbcon.php');
include('functions.php');
require_once 'session_tracker.php';
```

with:

```php
require_once __DIR__ . '/includes/bootstrap.php';
```

`$con` and `$dbh` are still available as globals, and every function that existed in `functions.php` still works via the shim aliases in `Helpers.php`.

---

### Preferred migration (new code)

Use the class APIs directly instead of the legacy functions:

```php
require_once __DIR__ . '/includes/bootstrap.php';

// Protect the page — redirects to login.php if not authenticated.
Auth::requireLogin();

// Read the current user.
$email = Auth::currentUser();

// Query helpers — no manual prepare/bind_param boilerplate.
$db   = Database::getInstance();
$user = $db->fetchOne("SELECT * FROM tblwriters WHERE email = ?", "s", $email);
$all  = $db->fetchAll("SELECT * FROM tblwriters WHERE is_online = ?", "i", 1);
$db->query("UPDATE tblwriters SET last_seen = NOW() WHERE email = ?", "s", $email);

// Transactions.
$db->transaction(function (Database $db) use ($taskId, $email) {
    $db->query("UPDATE tbltasks SET status = 'completed' WHERE id = ?", "i", $taskId);
    $db->query("INSERT INTO tblaudit (action, email) VALUES (?, ?)", "ss", 'complete', $email);
});

// CSRF protection on POST forms.
// In the form template:
echo csrfField();
// In the POST handler:
Auth::requireCsrf();
```

---

### Login page migration

```php
// Before (login.php):
$sql  = "SELECT email, password FROM tblwriters WHERE email = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 1) {
    $row = $result->fetch_assoc();
    if (password_verify($password, $row['password'])) {
        $_SESSION['sessionWriter'] = $email;
        updateUserStatus($email, true);
        // … remember-me token logic …
    }
}

// After:
require_once __DIR__ . '/includes/bootstrap.php';
Auth::redirectIfLoggedIn();

if (isPost()) {
    $email    = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']);

    if (Auth::login($email, $password, $remember, $error)) {
        redirect(safeRedirectUrl($_COOKIE['last_page_before_timeout'] ?? 'index.php'));
    }
    // $error now contains the failure message.
}
```

### Protected page migration

```php
// Before:
ob_start();
session_start();
include('dbcon.php');
include('functions.php');
check_login();   // or Auth::requireLogin();

// After:
require_once __DIR__ . '/includes/bootstrap.php';
Auth::requireLogin();
```

---

## Security improvements

| Issue | Fix |
|-------|-----|
| **SQL injection** — `functions.php` used string interpolation directly in queries (`"WHERE email = '$email'"`) | All DB access in the new files uses MySQLi prepared statements via `Database::query/fetchOne/fetchAll` |
| **Session fixation** — no `session_regenerate_id()` call on login | `Auth::login()` calls `session_regenerate_id(true)` before setting session variables |
| **CSRF** — no cross-site request forgery protection on state-changing forms | `Auth::csrfToken()`, `Auth::verifyCsrf()`, `Auth::requireCsrf()`, and the `csrfField()` template helper |
| **Insecure cookies** — `setcookie()` calls were missing `httponly`/`samesite` options | `Auth::secureCookie()` (private) always sets `HttpOnly`, `SameSite=Lax`, and `Secure` when on HTTPS |
| **Open redirect** — login redirect used raw cookie value without domain check | `safeRedirectUrl()` in `Helpers.php` rejects external hosts and `login.php` loops |
| **Redundant DB connections** — `dbcon.php` opened both PDO and MySQLi; `db.php` opened a second MySQLi | `Database` singleton opens each connection only once |
| **Password leak in session** — `login.php` stored raw password in `$_SESSION` | Removed; sessions now hold only the user's email |
| **Debug `error_log` calls left in production** — `login.php` had `error_log("Available cookies: …")` | Bootstrap's environment check suppresses display and controls log output per environment |

---

## Database helper reference

```php
$db = Database::getInstance();

// Execute and return the statement (check $stmt->affected_rows etc.).
$stmt = $db->query(string $sql, string $types = '', mixed ...$params);

// Fetch first matching row or null.
$row  = $db->fetchOne(string $sql, string $types = '', mixed ...$params);

// Fetch all matching rows.
$rows = $db->fetchAll(string $sql, string $types = '', mixed ...$params);

// Last inserted auto-increment ID.
$id   = $db->lastInsertId();

// Connections.
$con  = $db->getMysqli();   // MySQLi
$pdo  = $db->getPdo();      // PDO (lazy)

// Transactions.
$db->beginTransaction();
$db->commit();
$db->rollback();
$db->transaction(callable $callback);   // auto commit/rollback
```

---

## Auth reference

```php
// Authentication.
Auth::login(string $email, string $password, bool $remember, string &$error): bool
Auth::logout(): void
Auth::isLoggedIn(): bool
Auth::currentUser(): ?string
Auth::requireLogin(string $loginUrl = 'login.php'): void
Auth::redirectIfLoggedIn(string $destination = 'index.php'): void

// Passwords.
Auth::hashPassword(string $plain): string
Auth::verifyPassword(string $plain, string $hash): bool
Auth::needsRehash(string $hash): bool

// CSRF.
Auth::csrfToken(): string
Auth::verifyCsrf(string $submitted): bool
Auth::requireCsrf(string $fieldName = '_csrf'): void

// Online status.
Auth::updateOnlineStatus(string $email, bool $online): void
```

---

## Helpers reference

```php
// Flash messages (stored in $_SESSION).
setMessage(string $message): void
displayMessage(): void
setAlert(string $html): void
displayAlert(): void
setSubAlert(string $html): void
displaySubAlert(): void
validationErrors(string $errorMessage): void

// Redirection.
redirect(string $location): never
safeRedirectUrl(string $url, string $fallback = 'index.php'): string

// Sanitization.
sanitize(mixed $value): string
sanitizeFields(array $keys, array $source = []): array

// CSRF template helper.
csrfField(): string   // outputs <input type="hidden" name="_csrf" value="…">

// Time.
timeAgo(string $datetime): string
timeDueIn(string $datetime, int $showFullDateAfter = 31, bool $returnArray = false): string|array

// Files.
formatSizeUnits(int $bytes): string

// Text.
truncate(string $text, int $length = 100, string $suffix = '…'): string
toTitleCase(string $value): string

// Request.
isPost(): bool
isGet(): bool
isAjax(): bool
jsonResponse(mixed $data, int $status = 200): never

// Version.
getVersionNumber(): string
getVersionDescription(): string
getVersionLastUpdated(string $format = 'F j, Y'): string
```

---

## Next steps

1. **Add CSRF fields to all POST forms** — drop `<?= csrfField() ?>` inside every `<form>` tag and call `Auth::requireCsrf()` at the top of the handler.

2. **Migrate remaining pages to `bootstrap.php`** — search for files that still include `dbcon.php` or `db.php` directly and replace with the single bootstrap line.

3. **Audit the sudo/ admin app** — it has its own duplicated `updateUserStatus`, `check-login.php`, and session logic. The same refactoring pattern applies; `Database::getPdo()` replaces `$dbh`.

4. **Move DB credentials to environment variables** — replace the `define()` constants in `bootstrap.php` with `$_ENV`/`getenv()` reads so credentials are never in version control.

5. **Enable HTTPS + set `Secure` on all cookies** — `Auth::secureCookie()` already honours `$_SERVER['HTTPS']`; flipping the site to HTTPS activates it automatically.

6. **Consider a PSR-4 autoloader** — once the project grows, replace the manual `require_once` calls in `bootstrap.php` with Composer's autoloader so classes are resolved automatically.
