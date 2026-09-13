<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Enums\UserRole;
use App\Exceptions\DoctorNotFoundException;
use App\Http\Response;
use App\Repositories\DoctorRepository;
use App\Services\AuthorizationService;

/**
 * Handles HTTP requests related to doctors.
 */
class DoctorController
{
    public function __construct(
        private DoctorRepository $doctorRepository,
        private AuthorizationService $authorizationService
    ) {
    }

    /**
     * Retrieves a doctor by ID.
     *
     * Secretary and doctor can view doctor data.
     *
     * GET /api/doctors/{id}
     */
    public function show(int $id): void
    {
        $this->authorizationService->requireRole(
            UserRole::SECRETARY,
            UserRole::DOCTOR
        );

        $doctor = $this->doctorRepository->findById($id);

        if ($doctor === null) {
            throw new DoctorNotFoundException();
        }

        Response::json([
            'success' => true,
            'data' => $doctor
        ]);
    }
}