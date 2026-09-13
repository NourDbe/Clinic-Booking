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
     * This will be used later during login.
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