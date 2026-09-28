<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\LoginLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('admin.auth.login')->with('title', 'Masuk');
    }

    public function login(LoginRequest $request)
    {
        $this->ensureNotRateLimited($request);

        $credentials = $request->only('email', 'password');

        // Attempt but also check is_active after retrieval
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            /** @var User $user */
            $user = Auth::user();
            if (! $user->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                throw ValidationException::withMessages([
                    'email' => 'Akun Anda telah dinonaktifkan. Hubungi superadmin.',
                ]);
            }

            RateLimiter::clear($this->throttleKey($request));
            $request->session()->regenerate();

            $this->recordActivity(LoginLog::EVENT_LOGIN, $request);

            return redirect()->intended(route('admin.dashboard'))
                ->with('success', 'Selamat datang kembali, '.$user->name.'!');
        }

        RateLimiter::hit($this->throttleKey($request), 60);

        throw ValidationException::withMessages([
            'email' => __('auth.failed'),
        ]);
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            $this->recordActivity(LoginLog::EVENT_LOGOUT, $request);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')
            ->with('success', 'Anda telah berhasil keluar.');
    }

    private function recordActivity(string $event, Request $request): void
    {
        $user = Auth::user();
        $user?->loginLogs()->create([
            'event' => $event,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);
        if ($event === LoginLog::EVENT_LOGIN) {
            $user?->forceFill(['last_activity_at' => now()])->save();
        }
    }

    private function ensureNotRateLimited(Request $request): void
    {
        $key = $this->throttleKey($request);

        if (! RateLimiter::tooManyAttempts($key, 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($key);

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', ['seconds' => $seconds, 'minutes' => ceil($seconds / 60)]),
        ])->status(429);
    }

    private function throttleKey(Request $request): string
    {
        return Str::transliterate(Str::lower($request->input('email')).'|'.$request->ip());
    }
}
