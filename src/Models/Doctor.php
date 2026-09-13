<?php

declare(strict_types=1);

namespace App\Models;

use App\Abstracts\BaseEntity;
use App\Traits\HasTimestamps;

/**
 * Represents a doctor in the clinic.
 */
class Doctor extends BaseEntity
{
    use HasTimestamps;
    public function __construct(
        ?int $id,
        private string $name,
        private string $specialization
    ) {
        parent::__construct($id);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSpecialization(): string
    {
        return $this->specialization;
    }

    /**
     * Converts the doctor to an array suitable for API responses.
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'specialization' => $this->specialization,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}