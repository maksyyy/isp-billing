<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
public function handle($request, Closure $next, ...$roles)
{
    $userRole = strtolower(trim(auth()->user()->role));
    $roles = array_map(fn($r) => strtolower(trim($r)), $roles);

    if (!in_array($userRole, $roles)) {
        abort(403);
    }

    return $next($request);
}
}