<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Bookable;
use App\Enums\AppointmentStatus;
use App\Exceptions\AppointmentConflictException;
use App\Exceptions\DoctorNotFoundException;
use App\Exceptions\PatientNotFoundException;
use App\Exceptions\ValidationException;
use App\Models\Appointment;
use App\Repositories\AppointmentRepository;
use App\Repositories\DoctorRepository;
use App\Repositories\PatientRepository;

/**
 * Handles the appointment booking use case.
 *
 * This service coordinates booking validation,
 * related entity checks, conflict detection,
 * and persistence.
 */
class AppointmentBookingService implements Bookable
{
    public function __construct(
        private PatientRepository $patientRepository,
        private DoctorRepository $doctorRepository,
        private AppointmentRepository $appointmentRepository
    ) {
    }

    /**
     * Creates and stores a new appointment.
     */
    public function book(array $data): Appointment
    {
        // Validate request fields first.
        $this->validateRequiredFields($data);
        $this->validateDataTypes($data);

        $this->validateAppointmentDate(
            (string) $data['appointment_date']
        );

        $this->validateAppointmentTime(
            (string) $data['appointment_time']
        );

        $patientId = (int) $data['patient_id'];
        $doctorId = (int) $data['doctor_id'];
        $date = (string) $data['appointment_date'];
        $time = (string) $data['appointment_time'];

        // The patient must exist before booking.
        if ($this->patientRepository->findById($patientId) === null) {
            throw new PatientNotFoundException();
        }

        // The selected doctor must exist.
        if ($this->doctorRepository->findById($doctorId) === null) {
            throw new DoctorNotFoundException();
        }

        /**
         * Prevent double booking.
         *
         * Pending, confirmed and no-show appointments
         * block the slot. Cancelled appointments do not.
         */
        if (
            $this->appointmentRepository->hasActiveAppointmentAt(
                $doctorId,
                $date,
                $time
            )
        ) {
            throw new AppointmentConflictException();
        }

        /**
         * The appointment has no ID yet because
         * the database will generate it.
         */
        $appointment = new Appointment(
            id: null,
            patientId: $patientId,
            doctorId: $doctorId,
            appointmentDate: $date,
            appointmentTime: $time,
            status: AppointmentStatus::PENDING
        );

        // Store the appointment and return the persisted entity.
        return $this->appointmentRepository->create($appointment);
    }

    /**
     * Ensures that all required booking fields are present.
     */
    private function validateRequiredFields(array $data): void
    {
        $requiredFields = [
            'patient_id',
            'doctor_id',
            'appointment_date',
            'appointment_time',
        ];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                throw new ValidationException(
                    "The {$field} field is required."
                );
            }
        }
    }

    /**
     * Validates basic booking field types.
     */
    private function validateDataTypes(array $data): void
    {
        if (
            filter_var(
                $data['patient_id'],
                FILTER_VALIDATE_INT
            ) === false
        ) {
            throw new ValidationException(
                'patient_id must be an integer.'
            );
        }

        if (
            filter_var(
                $data['doctor_id'],
                FILTER_VALIDATE_INT
            ) === false
        ) {
            throw new ValidationException(
                'doctor_id must be an integer.'
            );
        }
    }

    /**
     * Validates that the appointment date uses
     * YYYY-MM-DD format and is not in the past.
     */
    private function validateAppointmentDate(string $date): void
    {
        $appointmentDate = \DateTimeImmutable::createFromFormat(
            'Y-m-d',
            $date
        );

        if (
            !$appointmentDate ||
            $appointmentDate->format('Y-m-d') !== $date
        ) {
            throw new ValidationException(
                'appointment_date must use YYYY-MM-DD format.'
            );
        }

        $today = new \DateTimeImmutable('today');

        if ($appointmentDate < $today) {
            throw new ValidationException(
                'Appointment date cannot be in the past.'
            );
        }
    }

    /**
     * Validates that the appointment time uses HH:MM format.
     */
    private function validateAppointmentTime(string $time): void
    {
        $appointmentTime = \DateTimeImmutable::createFromFormat(
            'H:i',
            $time
        );

        if (
            !$appointmentTime ||
            $appointmentTime->format('H:i') !== $time
        ) {
            throw new ValidationException(
                'appointment_time must use HH:MM format.'
            );
        }
    }
}