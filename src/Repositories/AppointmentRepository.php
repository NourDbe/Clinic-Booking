<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enums\AppointmentStatus;
use App\Exceptions\AppointmentConflictException;
use App\Models\Appointment;
use PDO;
use PDOException;

/**
 * Handles database operations related to appointments.
 *
 * The repository is responsible for persistence only:
 * reading, creating and updating appointment records.
 */
class AppointmentRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    /**
     * Finds an appointment by its ID.
     *
     * Returns the database row or null
     * when the appointment does not exist.
     */
    public function findById(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT
                id,
                patient_id,
                doctor_id,
                appointment_date,
                appointment_time,
                status,
                created_at,
                updated_at
             FROM appointments
             WHERE id = :id
             LIMIT 1'
        );

        $statement->execute([
            'id' => $id
        ]);

        $appointment = $statement->fetch();

        return $appointment ?: null;
    }

    /**
     * Checks whether the doctor already has an active
     * appointment at the requested date and time.
     *
     * Only PENDING and CONFIRMED appointments
     * occupy a booking slot.
     *
     * CANCELLED and NO_SHOW appointments
     * do not block that slot.
     */
    public function hasActiveAppointmentAt(
        int $doctorId,
        string $date,
        string $time
    ): bool {
        $statement = $this->pdo->prepare(
            'SELECT COUNT(*)
             FROM appointments
             WHERE doctor_id = :doctor_id
               AND appointment_date = :appointment_date
               AND appointment_time = :appointment_time
               AND status IN (:pending, :confirmed)'
        );

        $statement->execute([
            'doctor_id' => $doctorId,
            'appointment_date' => $date,
            'appointment_time' => $time,
            'pending' => AppointmentStatus::PENDING->value,
            'confirmed' => AppointmentStatus::CONFIRMED->value,
        ]);

        return (int) $statement->fetchColumn() > 0;
    }

    /**
     * Creates a new appointment.
     */
    public function create(Appointment $appointment): Appointment
    {
        try {
            $statement = $this->pdo->prepare(
                'INSERT INTO appointments (
                    patient_id,
                    doctor_id,
                    appointment_date,
                    appointment_time,
                    status
                 ) VALUES (
                    :patient_id,
                    :doctor_id,
                    :appointment_date,
                    :appointment_time,
                    :status
                 )'
            );

            $statement->execute([
                'patient_id' => $appointment->getPatientId(),
                'doctor_id' => $appointment->getDoctorId(),
                'appointment_date' => $appointment->getAppointmentDate(),
                'appointment_time' => $appointment->getAppointmentTime(),
                'status' => $appointment->getStatus()->value,
            ]);
        } catch (PDOException $exception) {
            /**
             * MySQL error 1062 means a UNIQUE constraint
             * was violated.
             *
             * This protects against two requests reaching
             * the database at almost exactly the same time.
             */
            $driverErrorCode = (int) (
                $exception->errorInfo[1] ?? 0
            );

            if ($driverErrorCode === 1062) {
                throw new AppointmentConflictException();
            }

            /**
             * Unknown database errors should not be hidden
             * as appointment conflicts.
             */
            throw $exception;
        }

        $saved = $this->findById(
            (int) $this->pdo->lastInsertId()
        );

        return $this->mapToAppointment($saved);
    }

    /**
     * Updates the status of an appointment.
     */
    public function updateStatus(
        int $appointmentId,
        AppointmentStatus $status
    ): void {
        $statement = $this->pdo->prepare(
            'UPDATE appointments
             SET status = :status
             WHERE id = :id'
        );

        $statement->execute([
            'status' => $status->value,
            'id' => $appointmentId,
        ]);
    }

    /**
     * Converts a database row into
     * an Appointment domain entity.
     */
    private function mapToAppointment(
        array $data
    ): Appointment {
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

        $appointment->setTimestamps(
            $data['created_at'] ?? null,
            $data['updated_at'] ?? null
        );

        return $appointment;
    }
}