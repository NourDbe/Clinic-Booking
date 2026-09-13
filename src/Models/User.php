<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UserRole;
use App\Traits\HasTimestamps;

/**
 * Represents an authenticated system user.
 *
 * User represents an account used to access the system,
 * not a clinic Person domain abstraction.
 */
class User
{
    use HasTimestamps;

    public function __construct(
        private ?int $id,
        private string $name,
        private string $email,
        private string $password,
        private UserRole $role
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Returns the hashed password.
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function getRole(): UserRole
    {
        return $this->role;
    }

    /**
     * Converts the user to API-safe data.
     *
     * Password is intentionally excluded.
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role->value,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}