<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Services\UserService;

/**
 * Handles HTTP requests related to system users.
 */
class UserController
{
    public function __construct(
        private UserService $userService
    ) {
    }

    /**
     * Creates a new system user.
     *
     * POST /api/users
     */
    public function store(Request $request): void
    {
        $data = $request->body();

        $user = $this->userService->create($data);

        Response::json([
            'success' => true,
            'message' => 'User created successfully.',
            'data' => $user->toArray()
        ], 201);
    }
}