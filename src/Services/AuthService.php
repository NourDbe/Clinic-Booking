<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ValidationException;
use App\Repositories\UserRepository;

/**
 * Handles authentication logic.
 */
class AuthService
{
    public function __construct(
        private UserRepository $userRepository
    ) {
    }

    /**
     * Validates user credentials.
     */
    public function login(array $data): array
    {
        $this->validate($data);

        $email = strtolower(
            trim((string) $data['email'])
        );

        $user = $this->userRepository->findByEmail($email);

        if ($user === null) {
            throw new ValidationException(
                'Invalid email or password.'
            );
        }

        /**
         * Compare the plain password from the request
         * with the hashed password stored in the database.
         */
        $passwordIsValid = password_verify(
            (string) $data['password'],
            (string) $user['password']
        );

       if (!$passwordIsValid) {
    throw new ValidationException(
        'Invalid email or password.'
    );
}

/**
 * Store authenticated user data in the session.
 */
$_SESSION['user'] = [
    'id' => (int) $user['id'],
    'name' => (string) $user['name'],
    'email' => (string) $user['email'],
    'role' => (string) $user['role'],
];

return $_SESSION['user'];
    }

    /**
     * Validates login input.
     */
    private function validate(array $data): void
    {
        if (
            !isset($data['email']) ||
            trim((string) $data['email']) === ''
        ) {
            throw new ValidationException(
                'Email is required.'
            );
        }

        if (
            !filter_var(
                $data['email'],
                FILTER_VALIDATE_EMAIL
            )
        ) {
            throw new ValidationException(
                'Email format is invalid.'
            );
        }

        if (
            !isset($data['password']) ||
            (string) $data['password'] === ''
        ) {
            throw new ValidationException(
                'Password is required.'
            );
        }
    }
}