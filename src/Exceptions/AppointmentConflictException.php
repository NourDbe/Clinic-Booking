<?php

declare(strict_types=1);

namespace App\Exceptions;

/**
 * Thrown when a requested appointment slot is already occupied.
 */
class AppointmentConflictException extends ApiException
{
    public function __construct()
    {
        parent::__construct(
            'Appointment slot is already booked.',
            409
        );
    }
}