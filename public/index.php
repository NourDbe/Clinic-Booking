<?php

declare(strict_types=1);

session_start();

require __DIR__ . '/../vendor/autoload.php';

use App\Controllers\AppointmentController;
use App\Controllers\AuthController;
use App\Controllers\DoctorController;
use App\Controllers\PatientController;
use App\Controllers\UserController;
use App\Database\Database;
use App\Exceptions\ApiException;
use App\Http\Request;
use App\Http\Response;
use App\Http\Router;
use App\Repositories\AppointmentRepository;
use App\Repositories\DoctorRepository;
use App\Repositories\PatientRepository;
use App\Repositories\UserRepository;
use App\Services\AppointmentBookingService;
use App\Services\AuthService;
use App\Services\AuthorizationService;
use App\Services\PatientService;
use App\Services\UserService;

/**
 * Global exception handler.
 *
 * Known API/domain exceptions return
 * their own HTTP status code and message.
 */
set_exception_handler(function (Throwable $exception): void {
    if ($exception instanceof ApiException) {
        Response::json([
            'success' => false,
            'message' => $exception->getMessage()
        ], $exception->getStatusCode());

        return;
    }

    /**
     * Unexpected exceptions are logged internally.
     * Technical details are not exposed to the client.
     */
    error_log((string) $exception);

    Response::json([
        'success' => false,
        'message' => 'Internal server error'
    ], 500);
});

/**
 * Create HTTP objects.
 */
$request = new Request();
$router = new Router();

/**
 * Create one shared PDO database connection.
 */
$pdo = Database::connection();

/**
 * Repositories.
 *
 * Repositories are responsible
 * for database access and SQL queries.
 */
$patientRepository = new PatientRepository(
    $pdo
);

$doctorRepository = new DoctorRepository(
    $pdo
);

$appointmentRepository = new AppointmentRepository(
    $pdo
);

$userRepository = new UserRepository(
    $pdo
);

/**
 * Services.
 *
 * Services contain application
 * and business logic.
 */
$bookingService = new AppointmentBookingService(
    $patientRepository,
    $doctorRepository,
    $appointmentRepository
);

$patientService = new PatientService(
    $patientRepository
);

$userService = new UserService(
    $userRepository
);

$authService = new AuthService(
    $userRepository
);

/**
 * Authorization service.
 *
 * Handles authentication checks
 * and role-based access control.
 */
$authorizationService = new AuthorizationService();

/**
 * Controllers.
 *
 * Controllers receive HTTP requests,
 * delegate work, and return JSON responses.
 */
$appointmentController = new AppointmentController(
    $bookingService,
    $appointmentRepository,
    $authorizationService
);

$patientController = new PatientController(
    $patientService,
    $patientRepository,
    $authorizationService
);

$doctorController = new DoctorController(
    $doctorRepository,
    $authorizationService
);

$userController = new UserController(
    $userService,
    $authorizationService
);

$authController = new AuthController(
    $authService
);

/**
 * Register all API routes.
 */
require __DIR__ . '/../routes/api.php';

/**
 * Dispatch the incoming request
 * after all routes have been registered.
 */
$router->dispatch($request);