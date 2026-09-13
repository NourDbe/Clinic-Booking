<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Enums\AppointmentStatus;
use App\Enums\UserRole;
use App\Exceptions\AppointmentNotFoundException;
use App\Http\Request;
use App\Http\Response;
use App\Models\Appointment;
use App\Repositories\AppointmentRepository;
use App\Services\AppointmentBookingService;
use App\Services\AuthorizationService;

/**
 * Handles HTTP requests related to appointments.
 */
class AppointmentController
{
    public function __construct(
        private AppointmentBookingService $bookingService,
        private AppointmentRepository $appointmentRepository,
        private AuthorizationService $authorizationService
    ) {
    }

    /**
     * Creates a new appointment.
     *
     * Only the secretary can create appointments.
     *
     * POST /api/appointments
     */
    public function store(Request $request): void
    {
        $this->authorizationService->requireRole(
            UserRole::SECRETARY
        );

        $data = $request->body();

        $appointment = $this->bookingService->book($data);

        Response::json([
            'success' => true,
            'message' => 'Appointment booked successfully.',
            'data' => $appointment->toArray()
        ], 201);
    }

    /**
     * Retrieves an appointment by ID.
     *
     * Secretary and doctor can view appointments.
     *
     * GET /api/appointments/{id}
     */
    public function show(int $id): void
    {
        $this->authorizationService->requireRole(
            UserRole::SECRETARY,
            UserRole::DOCTOR
        );

        $appointment = $this->appointmentRepository->findById($id);

        if ($appointment === null) {
            throw new AppointmentNotFoundException();
        }

        Response::json([
            'success' => true,
            'data' => $appointment
        ]);
    }

    /**
     * Confirms a pending appointment.
     *
     * Only the secretary can confirm appointments.
     *
     * PATCH /api/appointments/{id}/confirm
     */
    public function confirm(int $id): void
    {
        $this->authorizationService->requireRole(
            UserRole::SECRETARY
        );

        $data = $this->appointmentRepository->findById($id);

        if ($data === null) {
            throw new AppointmentNotFoundException();
        }

        $appointment = new Appointment(
            id: (int) $data['id'],
            patientId: (int) $data['patient_id'],
            doctorId: (int) $data['doctor_id'],
            appointmentDate: (string) $data['appointment_date'],
            appointmentTime: substr(
                (string) $data['appointment_time'],
                0,
                5
            ),
            status: AppointmentStatus::from(
                (string) $data['status']
            )
        );

        $appointment->confirm();

        $this->appointmentRepository->updateStatus(
            $id,
            $appointment->getStatus()
        );

        Response::json([
            'success' => true,
            'message' => 'Appointment confirmed successfully.',
            'data' => [
                'id' => $id,
                'status' => $appointment->getStatus()->value
            ]
        ]);
    }

    /**
     * Cancels an appointment.
     *
     * Only the secretary can cancel appointments.
     *
     * PATCH /api/appointments/{id}/cancel
     */
    public function cancel(int $id): void
    {
        $this->authorizationService->requireRole(
            UserRole::SECRETARY
        );

        $data = $this->appointmentRepository->findById($id);

        if ($data === null) {
            throw new AppointmentNotFoundException();
        }

        $appointment = new Appointment(
            id: (int) $data['id'],
            patientId: (int) $data['patient_id'],
            doctorId: (int) $data['doctor_id'],
            appointmentDate: (string) $data['appointment_date'],
            appointmentTime: substr(
                (string) $data['appointment_time'],
                0,
                5
            ),
            status: AppointmentStatus::from(
                (string) $data['status']
            )
        );

        $appointment->cancel();

        $this->appointmentRepository->updateStatus(
            $id,
            $appointment->getStatus()
        );

        Response::json([
            'success' => true,
            'message' => 'Appointment cancelled successfully.',
            'data' => [
                'id' => $id,
                'status' => $appointment->getStatus()->value
            ]
        ]);
    }

    /**
     * Marks an appointment as no-show.
     *
     * Only the doctor can mark no-show.
     *
     * PATCH /api/appointments/{id}/no-show
     */
    public function markNoShow(int $id): void
    {
        $this->authorizationService->requireRole(
            UserRole::DOCTOR
        );

        $data = $this->appointmentRepository->findById($id);

        if ($data === null) {
            throw new AppointmentNotFoundException();
        }

        $appointment = new Appointment(
            id: (int) $data['id'],
            patientId: (int) $data['patient_id'],
            doctorId: (int) $data['doctor_id'],
            appointmentDate: (string) $data['appointment_date'],
            appointmentTime: substr(
                (string) $data['appointment_time'],
                0,
                5
            ),
            status: AppointmentStatus::from(
                (string) $data['status']
            )
        );

        $appointment->markNoShow();

        $this->appointmentRepository->updateStatus(
            $id,
            $appointment->getStatus()
        );

        Response::json([
            'success' => true,
            'message' => 'Appointment marked as no-show successfully.',
            'data' => [
                'id' => $id,
                'status' => $appointment->getStatus()->value
            ]
        ]);
    }
}