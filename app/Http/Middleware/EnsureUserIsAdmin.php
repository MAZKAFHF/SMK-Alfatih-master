<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        abort_unless($user?->is_admin && $user->is_active, 403);

        if (
            blank($user->admin_code)
            && ! $request->routeIs('admin.code.*')
            && ! $request->routeIs('admin.logout')
        ) {
            return redirect()->route('admin.code.create');
        }

        return $next($request);
    }
}
