<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\UserRole;
use App\Exceptions\ValidationException;
use App\Models\User;
use App\Repositories\UserRepository;

/**
 * Handles user registration logic.
 */
class UserService
{
    public function __construct(
        private UserRepository $userRepository
    ) {
    }

    /**
     * Creates a new system user.
     */
    public function create(array $data): User
    {
        $this->validate($data);

        $email = strtolower(
            trim((string) $data['email'])
        );

        /**
         * Email must be unique.
         */
        if ($this->userRepository->findByEmail($email) !== null) {
            throw new ValidationException(
                'Email is already registered.'
            );
        }

        /**
         * Never store a raw password.
         */
        $hashedPassword = password_hash(
            (string) $data['password'],
            PASSWORD_DEFAULT
        );

        if ($hashedPassword === false) {
            throw new ValidationException(
                'Unable to process password.'
            );
        }

        $user = new User(
            id: null,
            name: trim((string) $data['name']),
            email: $email,
            password: $hashedPassword,
            role: UserRole::from(
                (string) $data['role']
            )
        );

        return $this->userRepository->create($user);
    }

    /**
     * Validates user registration data.
     */
    private function validate(array $data): void
    {
        if (
            !isset($data['name']) ||
            trim((string) $data['name']) === ''
        ) {
            throw new ValidationException(
                'User name is required.'
            );
        }

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

        if (strlen((string) $data['password']) < 8) {
            throw new ValidationException(
                'Password must be at least 8 characters.'
            );
        }

        if (
            !isset($data['role']) ||
            !in_array(
                $data['role'],
                array_column(
                    UserRole::cases(),
                    'value'
                ),
                true
            )
        ) {
            throw new ValidationException(
                'User role must be secretary or doctor.'
            );
        }

        if (strlen(trim((string) $data['name'])) > 120) {
            throw new ValidationException(
                'User name must not exceed 120 characters.'
            );
        }

        if (strlen(trim((string) $data['email'])) > 190) {
            throw new ValidationException(
                'Email must not exceed 190 characters.'
            );
        }
    }
}