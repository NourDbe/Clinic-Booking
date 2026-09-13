CREATE DATABASE IF NOT EXISTS `clinic-booking`
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE `clinic-booking`;

/*
|--------------------------------------------------------------------------
| Doctors
|--------------------------------------------------------------------------
|
| Stores clinic doctors.
|
*/
CREATE TABLE IF NOT EXISTS doctors (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    name VARCHAR(120) NOT NULL,

    specialization VARCHAR(120) NOT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
);

/*
|--------------------------------------------------------------------------
| Patients
|--------------------------------------------------------------------------
|
| Stores patients who can book clinic appointments.
|
*/
CREATE TABLE IF NOT EXISTS patients (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    name VARCHAR(120) NOT NULL,

    phone VARCHAR(30) NOT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
);

/*
|--------------------------------------------------------------------------
| Users
|--------------------------------------------------------------------------
|
| Stores authenticated system users.
|
| Current roles:
| - secretary
| - doctor
|
| Passwords must always be stored as hashes generated
| using PHP password_hash().
|
*/
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    name VARCHAR(120) NOT NULL,

    email VARCHAR(190) NOT NULL UNIQUE,

    password VARCHAR(255) NOT NULL,

    role ENUM(
        'secretary',
        'doctor'
    ) NOT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
);

/*
|--------------------------------------------------------------------------
| Appointments
|--------------------------------------------------------------------------
|
| Stores clinic bookings and their current status.
|
*/
CREATE TABLE IF NOT EXISTS appointments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    patient_id INT UNSIGNED NOT NULL,

    doctor_id INT UNSIGNED NOT NULL,

    appointment_date DATE NOT NULL,

    appointment_time TIME NOT NULL,

    status ENUM(
        'pending',
        'confirmed',
        'cancelled',
        'no_show'
    ) NOT NULL DEFAULT 'pending',

    /*
     * Only pending and confirmed appointments
     * occupy the booking slot.
     *
     * Cancelled and no-show appointments
     * release the slot.
     */
    active_slot TINYINT
    GENERATED ALWAYS AS (
        CASE
            WHEN status IN ('pending', 'confirmed')
                THEN 1
            ELSE NULL
        END
    ) STORED,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    /*
     * Each appointment must belong
     * to an existing patient.
     */
    CONSTRAINT fk_appointments_patient
        FOREIGN KEY (patient_id)
        REFERENCES patients(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    /*
     * Each appointment must belong
     * to an existing doctor.
     */
    CONSTRAINT fk_appointments_doctor
        FOREIGN KEY (doctor_id)
        REFERENCES doctors(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    /*
     * Database-level double booking protection.
     *
     * Prevents two pending/confirmed appointments
     * for the same doctor, date and time.
     */
    UNIQUE KEY uq_active_doctor_slot (
        doctor_id,
        appointment_date,
        appointment_time,
        active_slot
    )
);

/*
|--------------------------------------------------------------------------
| Initial Clinic Doctor
|--------------------------------------------------------------------------
|
| Inserts Dr Samer only if he does not already exist.
|
*/
INSERT INTO doctors (
    name,
    specialization
)
SELECT
    'Dr Samer',
    'Internal Medicine'
WHERE NOT EXISTS (
    SELECT 1
    FROM doctors
    WHERE name = 'Dr Samer'
);