<?php

declare(strict_types=1);

namespace App\Models;

use App\Abstracts\BaseEntity;
use App\Traits\HasTimestamps;

/**
 * Represents a patient in the clinic.
 */
class Patient extends BaseEntity
{
    use HasTimestamps;
    public function __construct(
        ?int $id,
        private string $name,
        private string $phone
    ) {
        parent::__construct($id);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    /**
     * Converts the patient to an array suitable for API responses.
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}