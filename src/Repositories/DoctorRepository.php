<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

/**
 * Handles database operations related to doctors.
 *
 * The repository keeps SQL queries outside
 * the business logic layer.
 */
class DoctorRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    /**
     * Finds a doctor by ID.
     *
     * Returns the doctor data as an associative array,
     * or null if the doctor does not exist.
     */
    public function findById(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT id, name, specialization, created_at, updated_at
             FROM doctors
             WHERE id = :id
             LIMIT 1'
        );

        $statement->execute([
            'id' => $id
        ]);

        $doctor = $statement->fetch();

        return $doctor ?: null;
    }
}