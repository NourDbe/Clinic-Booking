<?php

declare(strict_types=1);

namespace App\Database;

use PDO;

/**
 * Responsible for creating and providing
 * the PDO database connection.
 */
final class Database
{
    /**
     * Stores one shared PDO connection.
     */
    private static ?PDO $connection = null;

    /**
     * Returns the PDO connection.
     *
     * The connection is created only once
     * and reused during the current request.
     */
    public static function connection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        /**
         * Load database settings from the config file.
         */
        $config = require dirname(__DIR__, 2)
            . '/config/database.php';

        /**
         * DSN tells PDO how to connect to MySQL.
         */
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $config['host'],
            $config['port'],
            $config['database'],
            $config['charset']
        );

        self::$connection = new PDO(
            $dsn,
            $config['username'],
            $config['password'],
            [
                /**
                 * Throw exceptions when a database error occurs.
                 */
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,

                /**
                 * Return database rows as associative arrays.
                 */
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

                /**
                 * Use native prepared statements when possible.
                 */
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );

        return self::$connection;
    }
}