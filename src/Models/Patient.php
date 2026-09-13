<?php

declare(strict_types=1);

namespace App\Models;

use App\Abstracts\Person;

/**
 * Represents a clinic patient.
 */
class Patient extends Person
{
    public function __construct(
        ?int $id,
        string $name,
        private string $phone
    ) {
        parent::__construct(
            $id,
            $name
        );
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'phone' => $this->phone,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}