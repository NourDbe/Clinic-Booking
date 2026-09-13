<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enums\UserRole;
use App\Models\User;
use PDO;

/**
 * Handles database operations related to system users.
 */
class UserRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    /**
     * Finds a user by ID.
     */
    public function findById(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT
                id,
                name,
                email,
                password,
                role,
                created_at,
                updated_at
             FROM users
             WHERE id = :id
             LIMIT 1'
        );

        $statement->execute([
            'id' => $id
        ]);

        $user = $statement->fetch();

        return $user ?: null;
    }

    /**
     * Finds a user by email.
     *
     * Used during login and uniqueness checks.
     */
    public function findByEmail(string $email): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT
                id,
                name,
                email,
                password,
                role,
                created_at,
                updated_at
             FROM users
             WHERE email = :email
             LIMIT 1'
        );

        $statement->execute([
            'email' => $email
        ]);

        $user = $statement->fetch();

        return $user ?: null;
    }

    /**
     * Checks whether at least one system user exists.
     *
     * This is used during application bootstrap:
     * the first user may be created without authentication,
     * but later users require secretary authorization.
     */
    public function hasUsers(): bool
    {
        $statement = $this->pdo->query(
            'SELECT EXISTS(
                SELECT 1
                FROM users
                LIMIT 1
            )'
        );

        return (bool) $statement->fetchColumn();
    }

    /**
     * Creates a new system user.
     */
    public function create(User $user): User
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO users (
                name,
                email,
                password,
                role
             ) VALUES (
                :name,
                :email,
                :password,
                :role
             )'
        );

        $statement->execute([
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'password' => $user->getPassword(),
            'role' => $user->getRole()->value,
        ]);

        $saved = $this->findById(
            (int) $this->pdo->lastInsertId()
        );

        /**
         * This should normally never happen after
         * a successful INSERT, but it protects us
         * from accessing null as an array.
         */
        if ($saved === null) {
            throw new \RuntimeException(
                'Unable to retrieve created user.'
            );
        }

        $createdUser = new User(
            id: (int) $saved['id'],
            name: (string) $saved['name'],
            email: (string) $saved['email'],
            password: (string) $saved['password'],
            role: UserRole::from(
                (string) $saved['role']
            )
        );

        $createdUser->setTimestamps(
            $saved['created_at'] ?? null,
            $saved['updated_at'] ?? null
        );

        return $createdUser;
    }
}