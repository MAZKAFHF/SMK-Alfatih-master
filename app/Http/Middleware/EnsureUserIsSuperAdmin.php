<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        abort_unless($user?->is_superadmin && $user->is_active, 403);

        return $next($request);
    }
}
