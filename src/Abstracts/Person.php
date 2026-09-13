<?php

declare(strict_types=1);

namespace App\Abstracts;

use App\Traits\HasTimestamps;

/**
 * Represents a common abstraction for people in the clinic domain.
 *
 * Patient and Doctor are both people, so they share:
 * - ID
 * - Name
 * - Timestamps
 *
 * Their specific details remain in their own classes.
 */
abstract class Person
{
    use HasTimestamps;

    public function __construct(
        protected ?int $id,
        protected string $name
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
}