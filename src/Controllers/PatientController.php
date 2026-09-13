<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Enums\UserRole;
use App\Exceptions\PatientNotFoundException;
use App\Http\Request;
use App\Http\Response;
use App\Repositories\PatientRepository;
use App\Services\AuthorizationService;
use App\Services\PatientService;

/**
 * Handles HTTP requests related to patients.
 */
class PatientController
{
    public function __construct(
        private PatientService $patientService,
        private PatientRepository $patientRepository,
        private AuthorizationService $authorizationService
    ) {
    }

    /**
     * Registers a new patient.
     *
     * Only the secretary can register patients.
     *
     * POST /api/patients
     */
    public function store(Request $request): void
    {
        $this->authorizationService->requireRole(
            UserRole::SECRETARY
        );

        $data = $request->body();

        $patient = $this->patientService->create($data);

        Response::json([
            'success' => true,
            'message' => 'Patient registered successfully.',
            'data' => $patient->toArray()
        ], 201);
    }

    /**
     * Retrieves a patient by ID.
     *
     * Secretary and doctor can view patient data.
     *
     * GET /api/patients/{id}
     */
    public function show(int $id): void
    {
        $this->authorizationService->requireRole(
            UserRole::SECRETARY,
            UserRole::DOCTOR
        );

        $patient = $this->patientRepository->findById($id);

        if ($patient === null) {
            throw new PatientNotFoundException();
        }

        Response::json([
            'success' => true,
            'data' => $patient
        ]);
    }
}