<?php
declare(strict_types=1);

/**
 * /api/v1/auth
 *
 * POST /auth/login    — writer login
 * POST /auth/logout   — logout
 * GET  /auth/me       — current session info
 */

require_once dirname(__DIR__, 2) . '/includes/Service/UserService.php';

$service  = new UserService();
$subRoute = $parts[2] ?? $parts[1] ?? '';   // /auth/{subRoute}

// Override $subRoute: for /auth/login, $parts is ['auth','login'] so $id is null and $parts[1] = 'login'
$subRoute = $parts[1] ?? '';

switch ("$method:$subRoute") {

    // -----------------------------------------------------------------------
    case 'POST:login':
    // -----------------------------------------------------------------------
        $email    = trim($body['email']    ?? '');
        $password = trim($body['password'] ?? '');

        if (!$email || !$password) {
            apiResponse(['error' => 'email and password are required'], 422);
        }

        try {
            $user = $service->authenticateWriter($email, $password);
            session_regenerate_id(true);
            $_SESSION['sessionWriter'] = $user['email'];
            apiResponse([
                'message'  => 'Login successful.',
                'username' => $user['username'],
                'email'    => $user['email'],
            ]);
        } catch (InvalidArgumentException $e) {
            apiResponse(['error' => $e->getMessage()], 401);
        }

    // -----------------------------------------------------------------------
    case 'POST:logout':
    // -----------------------------------------------------------------------
        if (!empty($_SESSION['sessionWriter'])) {
            $service->logoutWriter($_SESSION['sessionWriter']);
        }
        apiResponse(['message' => 'Logged out.']);

    // -----------------------------------------------------------------------
    case 'GET:me':
    // -----------------------------------------------------------------------
        $email = requireAuth();
        $repo  = new UserRepository();
        $user  = $repo->getProfile($email);
        if (!$user) apiResponse(['error' => 'User not found'], 404);
        unset($user['password']);
        apiResponse($user);

    // -----------------------------------------------------------------------
    default:
        apiResponse(['error' => 'Not found'], 404);
}
