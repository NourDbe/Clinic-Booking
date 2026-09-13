<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Patient;
use PDO;

/**
 * Handles database operations related to patients.
 */
class PatientRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    /**
     * Finds a patient by ID.
     */
    public function findById(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT
                id,
                name,
                phone,
                created_at,
                updated_at
             FROM patients
             WHERE id = :id
             LIMIT 1'
        );

        $statement->execute([
            'id' => $id
        ]);

        $patient = $statement->fetch();

        return $patient ?: null;
    }

    /**
     * Creates a new patient.
     */
    public function create(Patient $patient): Patient
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO patients (
                name,
                phone
             ) VALUES (
                :name,
                :phone
             )'
        );

        $statement->execute([
            'name' => $patient->getName(),
            'phone' => $patient->getPhone(),
        ]);

        $saved = $this->findById(
            (int) $this->pdo->lastInsertId()
        );

        $createdPatient = new Patient(
            id: (int) $saved['id'],
            name: (string) $saved['name'],
            phone: (string) $saved['phone']
        );

        $createdPatient->setTimestamps(
            $saved['created_at'] ?? null,
            $saved['updated_at'] ?? null
        );

        return $createdPatient;
    }
}