<?php

declare(strict_types=1);

namespace App\Traits;

/**
 * Reusable timestamp behavior for domain entities.
 *
 * This trait is used by multiple unrelated classes
 * to avoid duplicating the same timestamp logic.
 */
trait HasTimestamps
{
    protected ?string $createdAt = null;
    protected ?string $updatedAt = null;

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }

    public function setTimestamps(
        ?string $createdAt,
        ?string $updatedAt
    ): void {
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    /**
     * Updates the last modified timestamp.
     */
    public function touch(): void
    {
        $this->updatedAt = date('Y-m-d H:i:s');
    }
}