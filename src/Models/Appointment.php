<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AppointmentStatus;
use App\Exceptions\InvalidAppointmentStatusException;
use App\Traits\HasTimestamps;

/**
 * Represents a clinic appointment.
 *
 * Appointment is not a Person,
 * so it owns its identifier directly.
 */
class Appointment
{
    use HasTimestamps;

    public function __construct(
        private ?int $id,
        private int $patientId,
        private int $doctorId,
        private string $appointmentDate,
        private string $appointmentTime,
        private AppointmentStatus $status
    ) {
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
        $this->touch();
    }

    /**
     * Cancels an active appointment.
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
        $this->touch();
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
        $this->touch();
    }

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