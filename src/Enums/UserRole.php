<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Defines the available roles for system users.
 */
enum UserRole: string
{
    case SECRETARY = 'secretary';
    case DOCTOR = 'doctor';
}