<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Enums\UserRole;
use App\Http\Request;
use App\Http\Response;
use App\Services\AuthorizationService;
use App\Services\UserService;

/**
 * Handles HTTP requests related to system users.
 */
class UserController
{
    public function __construct(
        private UserService $userService,
        private AuthorizationService $authorizationService
    ) {
    }

    /**
     * Creates a new system user.
     *
     * The first user can be created without authentication
     * to bootstrap the application.
     *
     * After that, only a secretary can create users.
     *
     * POST /api/users
     */
    public function store(Request $request): void
    {
        /**
         * If users already exist, user creation becomes
         * a protected secretary-only operation.
         */
        if ($this->userService->hasUsers()) {
            $this->authorizationService->requireRole(
                UserRole::SECRETARY
            );
        }

        $data = $request->body();

        $user = $this->userService->create($data);

        Response::json([
            'success' => true,
            'message' => 'User created successfully.',
            'data' => $user->toArray()
        ], 201);
    }
}