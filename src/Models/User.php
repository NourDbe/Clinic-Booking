<?php

declare(strict_types=1);

namespace App\Models;

use App\Abstracts\BaseEntity;
use App\Enums\UserRole;
use App\Traits\HasTimestamps;

/**
 * Represents an authenticated user of the clinic system.
 *
 * A user can currently be either:
 * - Secretary
 * - Doctor
 */
class User extends BaseEntity
{
    use HasTimestamps;

    public function __construct(
        ?int $id,
        private string $name,
        private string $email,
        private string $password,
        private UserRole $role
    ) {
        parent::__construct($id);
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
     *
     * The raw password should never be stored.
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
            'id' => $this->getId(),
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role->value,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}