<?php

declare(strict_types=1);

/**
 * Database configuration.
 *
 * Keeping database settings in a separate file
 * makes the connection reusable and easier to maintain.
 */
return [
    'host' => '127.0.0.1',
    'port' => '3306',
    'database' => 'clinic-booking',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
];