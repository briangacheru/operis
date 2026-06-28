<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/Repository/UserRepository.php';
require_once dirname(__DIR__) . '/Repository/AdminRepository.php';
require_once dirname(__DIR__) . '/Validator.php';

/**
 * UserService — business logic for writer and admin accounts.
 *
 * All methods throw InvalidArgumentException for validation errors and
 * RuntimeException for unexpected failures so callers can catch and convert
 * to HTTP responses as appropriate.
 */
class UserService
{
    private UserRepository  $users;
    private AdminRepository $admins;

    public function __construct(
        ?UserRepository  $users  = null,
        ?AdminRepository $admins = null
    ) {
        $this->users  = $users  ?? new UserRepository();
        $this->admins = $admins ?? new AdminRepository();
    }

    // -----------------------------------------------------------------------
    // Registration
    // -----------------------------------------------------------------------

    /** @throws InvalidArgumentException */
    public function registerWriter(array $input): int
    {
        $v = (new Validator($input))
            ->required(['username', 'email', 'password'])
            ->email('email')
            ->minLength('password', 8)
            ->maxLength('username', 50);

        if ($v->fails()) {
            throw new InvalidArgumentException(implode(' ', $v->allErrors()));
        }

        if ($this->users->emailExists($input['email'])) {
            throw new InvalidArgumentException('Email address is already registered.');
        }
        if ($this->users->usernameExists($input['username'])) {
            throw new InvalidArgumentException('Username is already taken.');
        }

        return $this->users->create([
            'username' => trim($input['username']),
            'email'    => strtolower(trim($input['email'])),
            'password' => password_hash($input['password'], PASSWORD_DEFAULT),
            'photo'    => $input['photo']   ?? null,
            'contact'  => $input['contact'] ?? null,
        ]);
    }

    // -----------------------------------------------------------------------
    // Authentication
    // -----------------------------------------------------------------------

    /** @throws InvalidArgumentException on bad credentials */
    public function authenticateWriter(string $email, string $password): array
    {
        $user = $this->users->findByEmail($email);
        if (!$user || !password_verify($password, $user['password'])) {
            throw new InvalidArgumentException('Invalid email or password.');
        }
        $this->users->setOnlineStatus($email, true);
        return $user;
    }

    /** @throws InvalidArgumentException on bad credentials */
    public function authenticateAdmin(string $email, string $password): array
    {
        $admin = $this->admins->findByEmail($email);
        if (!$admin || !password_verify($password, $admin['password'])) {
            throw new InvalidArgumentException('Invalid email or password.');
        }
        $this->admins->setOnlineStatus($email, true);
        return $admin;
    }

    // -----------------------------------------------------------------------
    // Password management
    // -----------------------------------------------------------------------

    /** @throws InvalidArgumentException */
    public function changeWriterPassword(string $email, string $current, string $new, string $confirm): void
    {
        if ($new !== $confirm) {
            throw new InvalidArgumentException('New passwords do not match.');
        }
        if (strlen($new) < 8) {
            throw new InvalidArgumentException('Password must be at least 8 characters.');
        }

        $user = $this->users->findByEmail($email);
        if (!$user || !password_verify($current, $user['password'])) {
            throw new InvalidArgumentException('Current password is incorrect.');
        }

        $this->users->updatePassword($email, password_hash($new, PASSWORD_DEFAULT));
    }

    // -----------------------------------------------------------------------
    // Profile
    // -----------------------------------------------------------------------

    /** @throws InvalidArgumentException */
    public function updateWriterProfile(string $email, array $input): void
    {
        $v = (new Validator($input))
            ->maxLength('username', 50)
            ->maxLength('contact', 20);

        if ($v->fails()) {
            throw new InvalidArgumentException(implode(' ', $v->allErrors()));
        }

        $this->users->updateProfile($email, array_intersect_key($input, array_flip(['username', 'contact', 'Photo', 'bio'])));
    }

    // -----------------------------------------------------------------------
    // Logout
    // -----------------------------------------------------------------------

    public function logoutWriter(string $email): void
    {
        $this->users->setOnlineStatus($email, false);
        session_unset();
        session_destroy();
        setcookie('PHPSESSID', '', time() - 3600, '/');
    }

    public function logoutAdmin(string $email): void
    {
        $this->admins->setOnlineStatus($email, false);
        session_unset();
        session_destroy();
        setcookie('PHPSESSID', '', time() - 3600, '/');
    }
}
