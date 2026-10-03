<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminRole
{
    public static function userCanAccess(Request $request): bool
    {
        $allowedRoles = collect(config('connect.admin_allowed_roles', []))
            ->map(fn ($role) => strtolower(trim((string) $role)))
            ->filter();

        $userRoles = collect($request->session()->get('sso_teams', []))
            ->map(function ($team) {
                if (is_array($team)) {
                    return $team['name'] ?? $team['role'] ?? $team['slug'] ?? null;
                }

                return $team;
            })
            ->map(fn ($role) => strtolower(trim((string) $role)))
            ->filter();

        return $allowedRoles->intersect($userRoles)->isNotEmpty();
    }

    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(self::userCanAccess($request), 403);

        return $next($request);
    }
}
