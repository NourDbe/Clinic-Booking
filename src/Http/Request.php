<?php

declare(strict_types=1);

namespace App\Http;

use App\Exceptions\ValidationException;

/**
 * Represents the incoming HTTP request.
 *
 * It provides simple access to:
 * - HTTP method
 * - Request path
 * - Query parameters
 * - JSON request body
 */
class Request
{
    /**
     * Returns the HTTP request method.
     */
    public function method(): string
    {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }

    /**
     * Returns the URL path without query parameters.
     */
    public function path(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        return parse_url($uri, PHP_URL_PATH) ?: '/';
    }

    /**
     * Returns a query parameter.
     */
    public function query(
        string $key,
        mixed $default = null
    ): mixed {
        return $_GET[$key] ?? $default;
    }

    /**
     * Reads and decodes the JSON request body.
     *
     * Invalid JSON is treated as a validation error
     * instead of silently returning an empty array.
     */
    public function body(): array
    {
        $rawBody = file_get_contents('php://input');

        /**
         * Empty body is allowed.
         * Validation of required fields is handled
         * later by the relevant service.
         */
        if ($rawBody === false || trim($rawBody) === '') {
            return [];
        }

        $data = json_decode($rawBody, true);

        /**
         * json_decode() does not throw automatically,
         * so we check whether decoding failed.
         */
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new ValidationException(
                'Invalid JSON body.'
            );
        }

        /**
         * Our API expects JSON data that can
         * be represented as an associative array.
         */
        if (!is_array($data)) {
            throw new ValidationException(
                'JSON body must contain valid data.'
            );
        }

        return $data;
    }
}