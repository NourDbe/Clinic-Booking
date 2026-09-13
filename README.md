# Clinic Booking API

A simple clinic appointment booking system built with **Pure PHP** and **Object-Oriented Programming**, without using any framework.

The system is designed for a private clinic and focuses on appointment booking, appointment status tracking, double-booking prevention, clean OOP design, and organized project structure.

---

## Main Features

- Register patients
- Retrieve patient information
- Retrieve doctor information
- Create appointments
- Retrieve appointments by ID
- Confirm appointments
- Cancel appointments
- Mark appointments as no-show
- Prevent double booking
- Validate request data
- Handle invalid JSON
- Handle errors using Exceptions
- Use PHP Enum for appointment status
- Use PHP Enum for user roles
- Use Interface, Abstract Class, Trait, and Closure
- Use Composer and PSR-4 autoloading
- Session-based authentication
- Login and logout
- Role-based authorization
- Secure password hashing with `password_hash()`
- Secure password verification with `password_verify()`
- Database-level protection against double booking

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
├── database/
│   └── schema.sql
├── public/
│   └── index.php
├── routes/
│   └── api.php
├── src/
│   ├── Abstracts/
│   │   └── Person.php
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
├── .gitignore
├── composer.json
├── composer.lock
└── README.md
```

---

# Domain Analysis & Design Decisions

The clinic problem was analyzed before creating the classes.

The main domain concepts are:

```text
Patient
Doctor
Appointment
```

`Appointment` is the central domain entity because it changes over time and contains business rules.

The appointment lifecycle is:

```text
pending -> confirmed
pending -> cancelled
confirmed -> cancelled
confirmed -> no_show
```

## Why an Abstract Class?

The project uses an abstract class named:

```text
Person
```

`Patient` and `Doctor` are fundamentally similar because both represent people in the clinic domain.

They share:

```text
id
name
timestamps
```

Their specialized data remains in their own classes:

```text
Patient -> phone
Doctor -> specialization
```

This makes the abstraction based on a real domain relationship instead of using inheritance only because classes share a technical field.

## Why an Interface?

The project contains:

```text
Bookable
```

It defines the booking contract:

```php
public function book(array $data): Appointment;
```

`AppointmentBookingService` implements this interface.

This separates the booking contract from its concrete implementation.

## Why a Trait?

The project contains:

```text
HasTimestamps
```

It provides reusable timestamp behavior:

```text
createdAt
updatedAt
setTimestamps()
touch()
```

The trait is used by multiple classes to avoid duplicating the same code.

## Why an Enum?

Appointment status has a limited and known set of values.

For that reason, the project uses:

```php
enum AppointmentStatus: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case CANCELLED = 'cancelled';
    case NO_SHOW = 'no_show';
}
```

User roles are also represented using an Enum:

```php
enum UserRole: string
{
    case SECRETARY = 'secretary';
    case DOCTOR = 'doctor';
}
```

## Why Exceptions?

The project uses Exceptions instead of `echo` or `die` for error handling.

Examples:

```text
ApiException
ValidationException
PatientNotFoundException
DoctorNotFoundException
AppointmentNotFoundException
AppointmentConflictException
InvalidAppointmentStatusException
```

A global exception handler converts known application exceptions into JSON responses with appropriate HTTP status codes.

## Why a Closure?

Closures are used as route callbacks.

A reusable Closure is also used in `routes/api.php` to validate positive route IDs and avoid repeating the same validation logic.

---

# Architecture

The project is organized into clear layers:

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

---

# Appointment Domain Behavior

Appointment state transitions are handled inside the `Appointment` entity.

Examples:

```php
$appointment->confirm();
$appointment->cancel();
$appointment->markNoShow();
```

Valid transitions include:

```text
pending -> confirmed
pending -> cancelled
confirmed -> cancelled
confirmed -> no_show
```

Invalid transitions throw `InvalidAppointmentStatusException`.

---

# Double Booking Prevention

The application prevents two active appointments from using the same doctor, date, and time.

Protection exists on two levels.

## Application Level

Before creating an appointment, the booking service checks whether the requested slot is already occupied.

These statuses block the slot:

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

The `appointments` table contains a generated column named:

```text
active_slot
```

A unique index protects the combination:

```text
doctor_id
appointment_date
appointment_time
active_slot
```

This protects against concurrent requests reaching the database at nearly the same time.

---

# Authentication

The application uses PHP Sessions for authentication.

Passwords are never stored as plain text.

During user creation:

```php
password_hash($password, PASSWORD_DEFAULT);
```

During login:

```php
password_verify($password, $storedHash);
```

## Create System User

```http
POST /api/users
```

The first system user can be created without authentication to bootstrap the application.

After at least one user exists, only a logged-in secretary can create additional users.

Example:

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

## Login

```http
POST /api/login
```

Example request:

```json
{
  "email": "secretary@clinic.com",
  "password": "Secret123"
}
```

## Current User

```http
GET /api/me
```

If not authenticated, the API returns `401 Unauthorized`.

## Logout

```http
POST /api/logout
```

---

# Role-Based Authorization

The application currently supports two roles:

```text
secretary
doctor
```

## Secretary Permissions

The secretary can:

- Register patients
- Create appointments
- View patients
- View doctors
- View appointments
- Confirm appointments
- Cancel appointments
- Create additional users

The secretary cannot mark an appointment as no-show.

## Doctor Permissions

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
- Create additional users

Unauthenticated protected requests return `401 Unauthorized`.

Authenticated users without the required role receive `403 Forbidden`.

---

# API Endpoints

## Health

```http
GET /api/health
```

## Users

```http
POST /api/users
POST /api/login
GET  /api/me
POST /api/logout
```

## Patients

```http
POST /api/patients
GET  /api/patients/{id}
```

## Doctors

```http
GET /api/doctors/{id}
```

## Appointments

```http
POST  /api/appointments
GET   /api/appointments/{id}
PATCH /api/appointments/{id}/confirm
PATCH /api/appointments/{id}/cancel
PATCH /api/appointments/{id}/no-show
```

---

# Validation

The API validates:

- Required fields
- Positive route IDs
- Patient ID
- Doctor ID
- Appointment date
- Appointment time
- Invalid JSON
- Existing patient
- Existing doctor
- User email
- User role
- Password length
- Unique email
- Appointment state transitions
- Double-booking conflicts

---

# HTTP Error Codes

The project commonly returns:

```text
401 Unauthorized
403 Forbidden
404 Not Found
409 Conflict
422 Unprocessable Entity
500 Internal Server Error
```

---

# Database

The schema is located at:

```text
database/schema.sql
```

The database contains:

```text
patients
doctors
appointments
users
```

The schema also inserts the initial clinic doctor:

```text
Dr Samer
Internal Medicine
```

---

# Installation

## 1. Clone the repository

```bash
git clone https://github.com/NourDbe/Clinic-Booking.git
cd Clinic-Booking
```

## 2. Install Composer dependencies

```bash
composer install
```

## 3. Create the database

Execute:

```text
database/schema.sql
```

using phpMyAdmin, MySQL, or MariaDB.

## 4. Configure the database

Edit:

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

## 5. Run the development server

```bash
php -S localhost:8000 -t public
```

API base URL:

```text
http://localhost:8000
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

After changing autoload configuration:

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

Recommended test flow:

```text
1. Create the first secretary user
2. Login as secretary
3. Register a patient
4. Create an appointment
5. Confirm the appointment
6. Logout
7. Login as doctor
8. View the appointment
9. Mark the confirmed appointment as no-show
```

Important error tests:

```text
Unauthenticated protected request -> 401
Wrong role -> 403
Missing resource -> 404
Duplicate active appointment -> 409
Invalid state transition -> 422
```

---

# Notes

This project intentionally uses **Pure PHP without a framework**.

Composer is used for dependency management and PSR-4 autoloading.

The project structure is framework-inspired while the implementation remains based on native PHP and OOP.

The core design demonstrates:

```text
Interface
Abstract Class
Trait
Enum
Exceptions
Closure
OOP
Services
Repositories
Controllers
PDO
PSR-4
Sessions
Authentication
Authorization
REST-style API
```

---

# Author

Clinic Booking System  
Pure PHP OOP Project
