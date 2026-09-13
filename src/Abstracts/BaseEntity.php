<?php

declare(strict_types=1);

namespace App\Abstracts;

/**
 * Base class for domain entities that have an identifier.
 *
 * It centralizes the shared ID behavior without forcing
 * unrelated business logic into child classes.
 */
abstract class BaseEntity
{
    public function __construct(
        protected ?int $id = null
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}