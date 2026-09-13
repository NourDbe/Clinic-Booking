<?php

declare(strict_types=1);

namespace App\Exceptions;

/**
 * Thrown when an invalid appointment status transition is attempted.
 */
class InvalidAppointmentStatusException extends ApiException
{
    public function __construct(string $message)
    {
        parent::__construct(
            $message,
            422
        );
    }
}