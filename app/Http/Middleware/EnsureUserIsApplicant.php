<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsApplicant
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        // Akun pemohon: login, aktif, dan BUKAN admin (mencegah privilege confusion)
        abort_unless($user && $user->is_active && ! $user->is_admin, 403, 'Akun admin tidak dapat mengakses portal pemohon.');

        return $next($request);
    }
}
