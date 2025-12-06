<?php

declare(strict_types=1);

namespace HRMS\Middleware;

use HRMS\Helpers\Response;

class Auth
{
    public static function requireAuth(): void
    {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
            Response::json(['error' => 'Unauthorized. Please login.'], 401);
        }
    }

    public static function requireHR(): void
    {
        self::requireAuth();
        if ($_SESSION['role'] !== 'HR') {
            Response::json(['error' => 'Access denied. HR admin only.'], 403);
        }
    }

    public static function requireEmployee(): void
    {
        self::requireAuth();
        if ($_SESSION['role'] !== 'EMP') {
            Response::json(['error' => 'Access denied. Employee only.'], 403);
        }
    }

    public static function getUserId(): int
    {
        return (int)($_SESSION['user_id'] ?? 0);
    }

    public static function getRole(): string
    {
        return $_SESSION['role'] ?? '';
    }
}

