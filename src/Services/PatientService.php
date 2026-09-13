<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ValidationException;
use App\Models\Patient;
use App\Repositories\PatientRepository;

/**
 * Handles patient-related business logic.
 */
class PatientService
{
    public function __construct(
        private PatientRepository $patientRepository
    ) {
    }

    /**
     * Registers a new patient.
     */
    public function create(array $data): Patient
    {
        $this->validate($data);

        $patient = new Patient(
            id: null,
            name: trim((string) $data['name']),
            phone: trim((string) $data['phone'])
        );

        return $this->patientRepository->create($patient);
    }

    /**
     * Validates patient registration data.
     */
    private function validate(array $data): void
    {
        if (
            !isset($data['name']) ||
            trim((string) $data['name']) === ''
        ) {
            throw new ValidationException(
                'Patient name is required.'
            );
        }

        if (
            !isset($data['phone']) ||
            trim((string) $data['phone']) === ''
        ) {
            throw new ValidationException(
                'Patient phone is required.'
            );
        }

        if (strlen(trim((string) $data['name'])) > 120) {
            throw new ValidationException(
                'Patient name must not exceed 120 characters.'
            );
        }

        if (strlen(trim((string) $data['phone'])) > 30) {
            throw new ValidationException(
                'Patient phone must not exceed 30 characters.'
            );
        }
    }
}