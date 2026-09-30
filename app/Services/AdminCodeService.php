<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AdminCodeService
{
    public static function verify(Request $request, ?User $user = null): void
    {
        $user ??= $request->user();
        abort_unless($user?->is_admin && filled($user->admin_code), 403);

        $data = $request->validate([
            'admin_code' => ['required', 'digits:4'],
        ], [], ['admin_code' => 'Kode admin']);

        $key = 'admin-code|'.$user->id.'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'admin_code' => 'Terlalu banyak kode yang salah. Coba lagi dalam '.RateLimiter::availableIn($key).' detik.',
            ])->status(429);
        }

        if (! Hash::check($data['admin_code'], $user->admin_code)) {
            RateLimiter::hit($key, 300);
            throw ValidationException::withMessages(['admin_code' => 'Kode admin salah.']);
        }

        RateLimiter::clear($key);
    }
}
