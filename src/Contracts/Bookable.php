<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Appointment;

/**
 * Defines the contract for any service that can create a booking.
 */
interface Bookable
{
    /**
     * Creates a new appointment from the provided booking data.
     */
    public function book(array $data): Appointment;
}