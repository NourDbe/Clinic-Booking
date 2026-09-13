<?php

declare(strict_types=1);

namespace App\Exceptions;

/**
 * Thrown when the requested appointment does not exist.
 */
class AppointmentNotFoundException extends ApiException
{
    public function __construct()
    {
        parent::__construct(
            'Appointment not found.',
            404
        );
    }
}