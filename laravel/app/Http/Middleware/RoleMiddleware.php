<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    private const HIERARCHY = [
        'client' => 1,
        'manager' => 2,
        'admin' => 3,
    ];

    public function handle(Request $request, Closure $next, string $minRole): Response
    {
        $user = auth('api')->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $userLevel = self::HIERARCHY[$user->role] ?? 0;
        $requiredLevel = self::HIERARCHY[$minRole] ?? PHP_INT_MAX;

        if ($userLevel < $requiredLevel) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return $next($request);
    }
}