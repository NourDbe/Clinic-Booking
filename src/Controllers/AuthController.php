<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Services\AuthService;

/**
 * Handles authentication requests.
 */
class AuthController
{
    public function __construct(
        private AuthService $authService
    ) {
    }

    /**
     * Authenticates a system user.
     *
     * POST /api/login
     */
    public function login(Request $request): void
    {
        $data = $request->body();

        $user = $this->authService->login($data);

        Response::json([
            'success' => true,
            'message' => 'Login successful.',
            'data' => $user
        ]);
    }

    /**
     * Returns the currently authenticated user.
     *
     * GET /api/me
     */
    public function me(): void
    {
        if (!isset($_SESSION['user'])) {
            Response::json([
                'success' => false,
                'message' => 'Unauthenticated.'
            ], 401);

            return;
        }

        Response::json([
            'success' => true,
            'data' => $_SESSION['user']
        ]);
    }

    /**
     * Logs out the current user.
     *
     * POST /api/logout
     */
    public function logout(): void
    {
        $_SESSION = [];

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }

        Response::json([
            'success' => true,
            'message' => 'Logout successful.'
        ]);
    }
}