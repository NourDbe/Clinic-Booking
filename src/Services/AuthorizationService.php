<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\UserRole;
use App\Exceptions\ApiException;

/**
 * Handles simple session-based authorization rules.
 */
class AuthorizationService
{
    /**
     * Ensures that a user is authenticated.
     */
    public function requireAuthenticated(): array
    {
        if (!isset($_SESSION['user'])) {
            throw new ApiException(
                'Unauthenticated.',
                401
            );
        }

        return $_SESSION['user'];
    }

    /**
     * Ensures that the authenticated user
     * has one of the allowed roles.
     */
    public function requireRole(UserRole ...$allowedRoles): array
    {
        $user = $this->requireAuthenticated();

        $currentRole = UserRole::from(
            (string) $user['role']
        );

        if (!in_array($currentRole, $allowedRoles, true)) {
            throw new ApiException(
                'Forbidden.',
                403
            );
        }

        return $user;
    }
}