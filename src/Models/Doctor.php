<?php

declare(strict_types=1);

namespace App\Models;

use App\Abstracts\Person;

/**
 * Represents a clinic doctor.
 */
class Doctor extends Person
{
    public function __construct(
        ?int $id,
        string $name,
        private string $specialization
    ) {
        parent::__construct(
            $id,
            $name
        );
    }

    public function getSpecialization(): string
    {
        return $this->specialization;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'specialization' => $this->specialization,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}