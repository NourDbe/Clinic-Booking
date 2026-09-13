<?php

declare(strict_types=1);

namespace App\Exceptions;

class DoctorNotFoundException extends ApiException
{
    public function __construct()
    {
        parent::__construct(
            'Doctor not found.',
            404
        );
    }
}