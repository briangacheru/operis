<?php
declare(strict_types=1);

/**
 * /api/v1/profile
 *
 * GET   /profile         — get authenticated writer's profile
 * PATCH /profile         — update profile fields
 * POST  /profile/password — change password
 */

require_once dirname(__DIR__, 2) . '/includes/Service/UserService.php';

$email   = requireAuth();
$service = new UserService();
$repo    = new UserRepository();

$subRoute = $parts[1] ?? '';

switch ("$method:$subRoute") {

    // -----------------------------------------------------------------------
    case 'GET:':
    // -----------------------------------------------------------------------
        $user = $repo->getProfile($email);
        if (!$user) apiResponse(['error' => 'Not found'], 404);
        apiResponse($user);

    // -----------------------------------------------------------------------
    case 'PATCH:':
    // -----------------------------------------------------------------------
        try {
            $service->updateWriterProfile($email, $body);
            apiResponse(['message' => 'Profile updated.']);
        } catch (InvalidArgumentException $e) {
            apiResponse(['error' => $e->getMessage()], 422);
        }

    // -----------------------------------------------------------------------
    case 'POST:password':
    // -----------------------------------------------------------------------
        try {
            $service->changeWriterPassword(
                $email,
                $body['current_password'] ?? '',
                $body['new_password']     ?? '',
                $body['confirm_password'] ?? ''
            );
            apiResponse(['message' => 'Password changed.']);
        } catch (InvalidArgumentException $e) {
            apiResponse(['error' => $e->getMessage()], 422);
        }

    // -----------------------------------------------------------------------
    default:
        apiResponse(['error' => 'Not found'], 404);
}
