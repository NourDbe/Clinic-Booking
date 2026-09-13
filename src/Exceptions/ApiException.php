<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Base exception for API/domain errors.
 * It carries the HTTP status code that should be returned to the client.
 */
class ApiException extends RuntimeException
{
    public function __construct(
        string $message,
        private int $statusCode = 400
    ) {
        parent::__construct($message);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}