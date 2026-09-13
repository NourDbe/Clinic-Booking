<?php

declare(strict_types=1);

namespace App\Exceptions;

class ValidationException extends ApiException
{
    public function __construct(string $message)
    {
        parent::__construct(
            $message,
            422
        );
    }
}