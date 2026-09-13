<?php

declare(strict_types=1);

use App\Exceptions\ValidationException;
use App\Http\Request;
use App\Http\Response;

/**
 * Converts a route parameter into a valid positive ID.
 *
 * This Closure avoids repeating the same validation
 * logic in every route that receives an {id}.
 */
$parsePositiveId = static function (
    string $value,
    string $resource
): int {
    if (!ctype_digit($value)) {
        throw new ValidationException(
            $resource . ' ID must be a positive integer.'
        );
    }

    $id = (int) $value;

    if ($id <= 0) {
        throw new ValidationException(
            $resource . ' ID must be a positive integer.'
        );
    }

    return $id;
};

/**
 * Health check.
 *
 * GET /api/health
 */
$router->get(
    '/api/health',
    function (Request $request): void {
        Response::json([
            'success' => true,
            'message' => 'Clinic Booking API is running'
        ]);
    }
);

/**
 * Register a new patient.
 *
 * POST /api/patients
 */
$router->post(
    '/api/patients',
    function (Request $request) use ($patientController): void {
        $patientController->store($request);
    }
);

/**
 * Get a patient by ID.
 *
 * GET /api/patients/{id}
 */
$router->get(
    '/api/patients/{id}',
    function (
        Request $request,
        array $params
    ) use (
        $patientController,
        $parsePositiveId
    ): void {
        $id = $parsePositiveId(
            $params['id'],
            'Patient'
        );

        $patientController->show($id);
    }
);

/**
 * Get a doctor by ID.
 *
 * GET /api/doctors/{id}
 */
$router->get(
    '/api/doctors/{id}',
    function (
        Request $request,
        array $params
    ) use (
        $doctorController,
        $parsePositiveId
    ): void {
        $id = $parsePositiveId(
            $params['id'],
            'Doctor'
        );

        $doctorController->show($id);
    }
);

/**
 * Create a new appointment.
 *
 * POST /api/appointments
 */
$router->post(
    '/api/appointments',
    function (Request $request) use ($appointmentController): void {
        $appointmentController->store($request);
    }
);

/**
 * Get an appointment by ID.
 *
 * GET /api/appointments/{id}
 */
$router->get(
    '/api/appointments/{id}',
    function (
        Request $request,
        array $params
    ) use (
        $appointmentController,
        $parsePositiveId
    ): void {
        $id = $parsePositiveId(
            $params['id'],
            'Appointment'
        );

        $appointmentController->show($id);
    }
);

/**
 * Confirm an appointment.
 *
 * PATCH /api/appointments/{id}/confirm
 */
$router->patch(
    '/api/appointments/{id}/confirm',
    function (
        Request $request,
        array $params
    ) use (
        $appointmentController,
        $parsePositiveId
    ): void {
        $id = $parsePositiveId(
            $params['id'],
            'Appointment'
        );

        $appointmentController->confirm($id);
    }
);

/**
 * Cancel an appointment.
 *
 * PATCH /api/appointments/{id}/cancel
 */
$router->patch(
    '/api/appointments/{id}/cancel',
    function (
        Request $request,
        array $params
    ) use (
        $appointmentController,
        $parsePositiveId
    ): void {
        $id = $parsePositiveId(
            $params['id'],
            'Appointment'
        );

        $appointmentController->cancel($id);
    }
);

/**
 * Mark an appointment as no-show.
 *
 * PATCH /api/appointments/{id}/no-show
 */
$router->patch(
    '/api/appointments/{id}/no-show',
    function (
        Request $request,
        array $params
    ) use (
        $appointmentController,
        $parsePositiveId
    ): void {
        $id = $parsePositiveId(
            $params['id'],
            'Appointment'
        );

        $appointmentController->markNoShow($id);
    }
);

/**
 * Create a new system user.
 *
 * POST /api/users
 */
$router->post('/api/users', function (Request $request) use ($userController) 
{
    return $userController->store($request);
});

/**
 * Login system user.
 *
 * POST /api/login
 */
$router->post(
    '/api/login',
    function (Request $request) use ($authController): void {
        $authController->login($request);
    }
);

/**
 * Get current authenticated user.
 *
 * GET /api/me
 */
$router->get(
    '/api/me',
    function (Request $request) use ($authController): void {
        $authController->me();
    }
);

/**
 * Logout current user.
 *
 * POST /api/logout
 */
$router->post(
    '/api/logout',
    function (Request $request) use ($authController): void {
        $authController->logout();
    }
);