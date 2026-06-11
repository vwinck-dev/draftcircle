<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class EnsureUserHasRole
{
    /** @param Closure(Request): Response $next */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $allowedRoles = array_map(UserRole::from(...), $roles);

        abort_unless($request->user() !== null && in_array($request->user()->role, $allowedRoles, true), 403);

        return $next($request);
    }
}
