<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AppointmentStatus;
use App\Exceptions\InvalidAppointmentStatusException;
use App\Abstracts\BaseEntity;
use App\Traits\HasTimestamps;

/**
 * Represents a clinic appointment.
 *
 * The entity is responsible for protecting its own state
 * and preventing invalid status transitions.
 */
class Appointment extends BaseEntity
{
    use HasTimestamps;
    public function __construct(
        ?int $id,
        private int $patientId,
        private int $doctorId,
        private string $appointmentDate,
        private string $appointmentTime,
        private AppointmentStatus $status
    ) {
        parent::__construct($id);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPatientId(): int
    {
        return $this->patientId;
    }

    public function getDoctorId(): int
    {
        return $this->doctorId;
    }

    public function getAppointmentDate(): string
    {
        return $this->appointmentDate;
    }

    public function getAppointmentTime(): string
    {
        return $this->appointmentTime;
    }

    public function getStatus(): AppointmentStatus
    {
        return $this->status;
    }

    /**
     * Confirms a pending appointment.
     */
    public function confirm(): void
    {
        if ($this->status !== AppointmentStatus::PENDING) {
            throw new InvalidAppointmentStatusException(
                'Only pending appointments can be confirmed.'
            );
        }

        $this->status = AppointmentStatus::CONFIRMED;
    }

    /**
     * Cancels an appointment that is still active.
     */
    public function cancel(): void
    {
        if ($this->status === AppointmentStatus::CANCELLED) {
            throw new InvalidAppointmentStatusException(
                'Appointment is already cancelled.'
            );
        }

        if ($this->status === AppointmentStatus::NO_SHOW) {
            throw new InvalidAppointmentStatusException(
                'A no-show appointment cannot be cancelled.'
            );
        }

        $this->status = AppointmentStatus::CANCELLED;
    }

    /**
     * Marks a confirmed appointment as no-show.
     */
    public function markNoShow(): void
    {
        if ($this->status !== AppointmentStatus::CONFIRMED) {
            throw new InvalidAppointmentStatusException(
                'Only confirmed appointments can be marked as no-show.'
            );
        }

        $this->status = AppointmentStatus::NO_SHOW;
    }

    /**
     * Converts the entity to an array suitable for API responses.
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'patient_id' => $this->patientId,
            'doctor_id' => $this->doctorId,
            'appointment_date' => $this->appointmentDate,
            'appointment_time' => $this->appointmentTime,
            'status' => $this->status->value,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}