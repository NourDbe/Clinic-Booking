# Clinic Booking API

A simple clinic appointment booking system built with **Pure PHP** and **Object-Oriented Programming**, without using any framework.

The project provides a REST-style API for managing patients, doctors, appointments, and authenticated clinic users. It applies clear business rules such as appointment status transitions, double-booking prevention, session-based authentication, and role-based authorization.

---

## Features

- Register new patients
- Retrieve patient information
- Retrieve doctor information
- Create appointments
- Retrieve appointments by ID
- Confirm appointments
- Cancel appointments
- Mark appointments as no-show
- Prevent double booking
- Validate request data and route IDs
- Handle invalid JSON
- Custom exception-based error handling
- PHP Enum for appointment statuses
- PHP Enum for user roles
- Interface, Abstract Class, Trait, and Closure usage
- Composer with PSR-4 autoloading
- Session-based authentication
- Login and logout
- Current authenticated user endpoint
- Role-based authorization
- Secure password hashing using `password_hash()`
- Secure password verification using `password_verify()`
- Database-level protection against concurrent double booking

---

## Technologies

- PHP 8.2+
- MySQL / MariaDB
- PDO
- Composer
- PSR-4 Autoloading
- PHP Sessions
- Pure PHP
- REST-style API

---

## Project Structure

```text
Clinic-Booking/
│
├── config/
│   └── database.php
│
├── database/
│   └── schema.sql
│
├── public/
│   └── index.php
│
├── routes/
│   └── api.php
│
├── src/
│   ├── Abstracts/
│   │   └── BaseEntity.php
│   ├── Contracts/
│   │   └── Bookable.php
│   ├── Controllers/
│   │   ├── AppointmentController.php
│   │   ├── AuthController.php
│   │   ├── DoctorController.php
│   │   ├── PatientController.php
│   │   └── UserController.php
│   ├── Database/
│   │   └── Database.php
│   ├── Enums/
│   │   ├── AppointmentStatus.php
│   │   └── UserRole.php
│   ├── Exceptions/
│   │   ├── ApiException.php
│   │   ├── AppointmentConflictException.php
│   │   ├── AppointmentNotFoundException.php
│   │   ├── DoctorNotFoundException.php
│   │   ├── InvalidAppointmentStatusException.php
│   │   ├── PatientNotFoundException.php
│   │   └── ValidationException.php
│   ├── Http/
│   │   ├── Request.php
│   │   ├── Response.php
│   │   └── Router.php
│   ├── Models/
│   │   ├── Appointment.php
│   │   ├── Doctor.php
│   │   ├── Patient.php
│   │   └── User.php
│   ├── Repositories/
│   │   ├── AppointmentRepository.php
│   │   ├── DoctorRepository.php
│   │   ├── PatientRepository.php
│   │   └── UserRepository.php
│   ├── Services/
│   │   ├── AppointmentBookingService.php
│   │   ├── AuthService.php
│   │   ├── AuthorizationService.php
│   │   ├── PatientService.php
│   │   └── UserService.php
│   └── Traits/
│       └── HasTimestamps.php
│
├── .gitignore
├── composer.json
├── composer.lock
└── README.md
```

---

## Installation

### 1. Clone the repository

```bash
git clone YOUR_REPOSITORY_URL
```

Then enter the project directory:

```bash
cd Clinic-Booking
```

### 2. Install Composer dependencies

```bash
composer install
```

Composer generates the PSR-4 autoloader inside:

```text
vendor/
```

### 3. Create the database

Open phpMyAdmin, MySQL, or MariaDB and execute:

```text
database/schema.sql
```

The schema creates the database:

```text
clinic-booking
```

and the tables:

```text
patients
doctors
appointments
users
```

It also inserts the initial clinic doctor:

```text
Dr Samer
Internal Medicine
```

### 4. Configure the database connection

Open:

```text
config/database.php
```

Example:

```php
<?php

declare(strict_types=1);

return [
    'host' => '127.0.0.1',
    'port' => '3306',
    'database' => 'clinic-booking',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
];
```

Update the username, password, and port according to your local database configuration.

### 5. Run the PHP development server

From the project root:

```bash
php -S localhost:8000 -t public
```

The API will be available at:

```text
http://localhost:8000
```

---

# Authentication

The application uses **PHP Sessions** for authentication.

After a successful login, basic user information is stored in the session.

Passwords are never stored as plain text.

During user creation:

```php
password_hash($password, PASSWORD_DEFAULT);
```

During login:

```php
password_verify($password, $storedHash);
```

---

## Create System User

```http
POST /api/users
```

Example request:

```json
{
  "name": "Clinic Secretary",
  "email": "secretary@clinic.com",
  "password": "Secret123",
  "role": "secretary"
}
```

Available roles:

```text
secretary
doctor
```

Example doctor user:

```json
{
  "name": "Dr Samer User",
  "email": "doctor@clinic.com",
  "password": "Doctor123",
  "role": "doctor"
}
```

Passwords are hashed before being stored in the database.

---

## Login

```http
POST /api/login
```

Request:

```json
{
  "email": "secretary@clinic.com",
  "password": "Secret123"
}
```

Successful response:

```json
{
  "success": true,
  "message": "Login successful.",
  "data": {
    "id": 1,
    "name": "Clinic Secretary",
    "email": "secretary@clinic.com",
    "role": "secretary"
  }
}
```

---

## Current Authenticated User

```http
GET /api/me
```

If authenticated:

```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Clinic Secretary",
    "email": "secretary@clinic.com",
    "role": "secretary"
  }
}
```

If not authenticated:

```json
{
  "success": false,
  "message": "Unauthenticated."
}
```

HTTP status:

```text
401 Unauthorized
```

---

## Logout

```http
POST /api/logout
```

Response:

```json
{
  "success": true,
  "message": "Logout successful."
}
```

---

# Role-Based Authorization

The application supports two authenticated system roles.

## Secretary

The secretary can:

- Register patients
- Create appointments
- View patients
- View doctors
- View appointments
- Confirm appointments
- Cancel appointments

The secretary cannot mark an appointment as no-show.

## Doctor

The doctor can:

- View patients
- View doctor information
- View appointments
- Mark confirmed appointments as no-show

The doctor cannot:

- Register patients
- Create appointments
- Confirm appointments
- Cancel appointments

Unauthenticated requests to protected endpoints return:

```text
401 Unauthorized
```

Authenticated users without the required role receive:

```text
403 Forbidden
```

---

# API Endpoints

## Health Check

```http
GET /api/health
```

Response:

```json
{
  "success": true,
  "message": "Clinic Booking API is running"
}
```

---

# Patients

## Register Patient

Secretary only.

```http
POST /api/patients
```

Request:

```json
{
  "name": "Nour Ahmad",
  "phone": "0999999999"
}
```

HTTP status on success:

```text
201 Created
```

## Get Patient

Secretary or doctor.

```http
GET /api/patients/{id}
```

If the patient does not exist:

```json
{
  "success": false,
  "message": "Patient not found."
}
```

---

# Doctors

## Get Doctor

Secretary or doctor.

```http
GET /api/doctors/{id}
```

If the doctor does not exist:

```json
{
  "success": false,
  "message": "Doctor not found."
}
```

---

# Appointments

## Create Appointment

Secretary only.

```http
POST /api/appointments
```

Request:

```json
{
  "patient_id": 1,
  "doctor_id": 1,
  "appointment_date": "2026-09-20",
  "appointment_time": "10:30"
}
```

A newly created appointment starts with:

```text
pending
```

## Get Appointment

Secretary or doctor.

```http
GET /api/appointments/{id}
```

## Confirm Appointment

Secretary only.

```http
PATCH /api/appointments/{id}/confirm
```

Valid transition:

```text
pending -> confirmed
```

## Cancel Appointment

Secretary only.

```http
PATCH /api/appointments/{id}/cancel
```

Valid transitions:

```text
pending -> cancelled
confirmed -> cancelled
```

A cancelled appointment releases the booking slot.

## Mark Appointment as No-Show

Doctor only.

```http
PATCH /api/appointments/{id}/no-show
```

Valid transition:

```text
confirmed -> no_show
```

A pending or cancelled appointment cannot be marked as no-show.

---

# Appointment Statuses

Appointment statuses are implemented using a PHP Enum:

```php
enum AppointmentStatus: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case CANCELLED = 'cancelled';
    case NO_SHOW = 'no_show';
}
```

Main lifecycle:

```text
pending
   |
   +----> confirmed
   |          |
   |          +----> cancelled
   |          |
   |          +----> no_show
   |
   +----> cancelled
```

---

# User Roles

User roles are implemented using a PHP Enum:

```php
enum UserRole: string
{
    case SECRETARY = 'secretary';
    case DOCTOR = 'doctor';
}
```

---

# Double Booking Prevention

The application prevents two active appointments from using the same doctor, date, and time.

Protection exists on two levels.

## Application Level

Before creating an appointment, the booking service checks whether the requested slot is already occupied.

Only these statuses block the slot:

```text
pending
confirmed
```

These statuses release the slot:

```text
cancelled
no_show
```

## Database Level

The `appointments` table contains a generated `active_slot` column and a unique index on:

```text
doctor_id
appointment_date
appointment_time
active_slot
```

This also protects against two booking requests reaching the database at nearly the same time.

---

# Validation

The API validates:

- Required fields
- Patient ID
- Doctor ID
- Appointment date
- Appointment time
- Positive route IDs
- Invalid JSON input
- Existing patient
- Existing doctor
- Valid user email
- User role
- Password length
- Unique email
- Appointment state transitions
- Double-booking conflicts

---

# Error Handling

The project uses custom Exceptions instead of `die()` or scattered error output.

Custom exceptions include:

```text
ApiException
ValidationException
PatientNotFoundException
DoctorNotFoundException
AppointmentNotFoundException
AppointmentConflictException
InvalidAppointmentStatusException
```

Common HTTP statuses:

```text
401 Unauthorized
403 Forbidden
404 Not Found
409 Conflict
422 Unprocessable Entity
500 Internal Server Error
```

Unexpected errors are logged internally and are not exposed to the API client.

---

# OOP Concepts Used

## Interface

`Bookable` defines the booking contract:

```php
public function book(array $data): Appointment;
```

`AppointmentBookingService` implements this interface.

## Abstract Class

`BaseEntity` provides shared ID behavior and is extended by:

```text
Patient
Doctor
Appointment
User
```

## Trait

`HasTimestamps` provides reusable timestamp behavior to multiple entities.

## Enums

The project uses:

```text
AppointmentStatus
UserRole
```

## Closure

Closures are used as route callbacks.

A reusable Closure is also used in `routes/api.php` to validate positive route IDs.

---

# Architecture

```text
HTTP Request
    |
    v
Router
    |
    v
Controller
    |
    v
Service
    |
    v
Repository
    |
    v
Database
```

Authentication and authorization are handled by:

```text
AuthService
AuthorizationService
```

Appointment state behavior remains inside the `Appointment` entity:

```php
$appointment->confirm();
$appointment->cancel();
$appointment->markNoShow();
```

---

# Composer and PSR-4

Composer is used for PSR-4 autoloading.

The namespace:

```text
App\
```

maps to:

```text
src/
```

After changing autoload configuration, run:

```bash
composer dump-autoload
```

---

# Testing

The API can be tested using:

- Thunder Client
- Postman
- Insomnia
- cURL

Recommended flow:

```text
1. Create secretary user
2. Login as secretary
3. Register patient
4. Create appointment
5. Confirm appointment
6. Logout
7. Login as doctor
8. View appointment
9. Mark confirmed appointment as no-show
```

Authorization tests:

```text
Unauthenticated protected request -> 401
Wrong role -> 403
Duplicate active appointment -> 409
Invalid state transition -> 422
Missing resource -> 404
```

---

# Notes

This project intentionally uses **Pure PHP without a framework**.

Composer is used for dependency management and PSR-4 autoloading.

The architecture is framework-inspired while keeping the implementation based entirely on native PHP and OOP.

---

# Author

Clinic Booking System  
Pure PHP OOP Project
