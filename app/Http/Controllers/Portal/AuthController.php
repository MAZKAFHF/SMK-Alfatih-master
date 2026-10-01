<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\LoginLog;
use App\Models\User;
use App\Services\AuditService;
use App\Services\MailService;
use App\Services\PpdbAvailability;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showRegister()
    {
        // Pemohon yang sudah login -> langsung ke dashboard, bukan form lagi.
        // Admin yang login -> tetap boleh lihat form (untuk mendaftarkan akun
        // pemohon), dengan notifikasi di view bahwa sesi admin akan diganti.
        if (auth()->check() && ! auth()->user()->is_admin) {
            return redirect()->route('portal.dashboard');
        }
        if (PpdbAvailability::resolvePublic()->status === PpdbAvailability::COMPLETED) {
            return redirect()->route('ppdb.index')->with('info', 'PPDB telah selesai. Akun baru akan tersedia pada periode berikutnya.');
        }

        return view('portal.auth.register')->with('title', 'Buat Akun PPDB');
    }

    public function register(Request $request)
    {
        $normalizedEmail = strtolower(trim((string) $request->input('email')));
        $keys = [
            'portal-register-ip|'.$request->ip(),
            'portal-register-account|'.hash('sha256', $normalizedEmail.'|'.$request->ip()),
        ];
        if ($limitedKey = collect($keys)->first(fn (string $key) => RateLimiter::tooManyAttempts($key, 5))) {
            throw ValidationException::withMessages(['email' => 'Terlalu banyak percobaan. Coba lagi dalam '.RateLimiter::availableIn($limitedKey).' detik.']);
        }
        foreach ($keys as $key) {
            RateLimiter::hit($key, 60);
        }

        if (PpdbAvailability::resolvePublic()->status === PpdbAvailability::COMPLETED) {
            throw ValidationException::withMessages(['email' => 'PPDB telah selesai. Akun baru akan tersedia pada periode berikutnya.']);
        }

        // Jika ada sesi login (mis. admin), keluar dulu agar akun pemohon
        // baru menjadi sesi aktif — bukan menumpuk / memantul ke /admin.
        if (auth()->check()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)->letters()->numbers()],
        ], [], ['name' => 'Nama', 'email' => 'Email', 'phone' => 'No. HP', 'password' => 'Password']);

        $user = User::create([
            'name' => trim($data['name']),
            'email' => strtolower(trim($data['email'])),
            'phone' => $data['phone'] ?? null,
            'password' => $data['password'],
            'is_admin' => false,
            'is_superadmin' => false,
            'is_active' => true,
            'is_applicant' => true,
        ]);

        event(new Registered($user));
        AuditService::log('portal_register', $user);

        // Kirim verifikasi email (signed URL 24 jam). Gagal kirim tidak menggagalkan akun.
        $verifyUrl = URL::temporarySignedRoute('portal.verification.verify', now()->addHours(24), ['id' => $user->id, 'hash' => sha1($user->email)]);
        MailService::send('verify_email', $user->email, 'Verifikasi Email PPDB SMK Tahfizh Al-Fatih', [
            'headline' => 'Verifikasi Email Anda',
            'preheader' => 'Satu klik untuk melanjutkan pendaftaran.',
            'body' => '<p>Halo <strong>'.e($user->name).'</strong>,</p><p>Akun PPDB Anda berhasil dibuat. Klik tombol di bawah untuk memverifikasi email dalam 24 jam. Akun dan Portal tetap dapat diakses kapan saja; pembuatan aplikasi siswa baru tersedia saat periode PPDB dibuka dan kuota masih ada.</p>',
            'cta' => 'Verifikasi Email',
            'cta_url' => $verifyUrl,
        ]);

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->route('portal.dashboard')->with('success', 'Akun berhasil dibuat. Kami mengirim link verifikasi ke email Anda — verifikasi sebelum kirim final.');
    }

    public function showLogin()
    {
        // Pemohon yang sudah login -> langsung ke dashboard.
        // Admin yang login -> tetap boleh lihat form login portal (untuk
        // beralih akun), dengan notifikasi di view.
        if (auth()->check() && ! auth()->user()->is_admin) {
            return redirect()->route('portal.dashboard');
        }
        if (PpdbAvailability::resolvePublic()->status === PpdbAvailability::COMPLETED) {
            return redirect()->route('ppdb.index')->with('info', 'PPDB telah selesai. Masuk portal tersedia kembali pada periode berikutnya.');
        }

        return view('portal.auth.login')->with('title', 'Masuk Portal PPDB');
    }

    public function login(Request $request)
    {
        $rawAttemptedEmail = trim((string) $request->input('email'));
        $normalizedEmail = strtolower($rawAttemptedEmail);
        $ipKey = 'portal-login-ip|'.$request->ip();
        $accountKey = 'portal-login-account|'.hash('sha256', $normalizedEmail);
        $limitedKey = collect([$ipKey, $accountKey])->first(
            fn (string $key) => RateLimiter::tooManyAttempts($key, $key === $ipKey ? 20 : 6)
        );
        if ($limitedKey) {
            $this->recordLoginAttempt($request, LoginLog::EVENT_LOGIN_FAILED, 'rate_limited');
            throw ValidationException::withMessages([
                'email' => 'Terlalu banyak percobaan login. Coba lagi dalam '.RateLimiter::availableIn($limitedKey).' detik.',
            ])->status(429);
        }

        $data = $request->validate([
            'email' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ]);

        $rawAttemptedEmail = trim($data['email']);
        $user = User::where('email', strtolower($rawAttemptedEmail))->first();
        if (! $user || ! Hash::check($data['password'], $user->password)) {
            RateLimiter::hit($ipKey, 60);
            RateLimiter::hit($accountKey, 300);
            $this->recordLoginAttempt($request, LoginLog::EVENT_LOGIN_FAILED, 'invalid_credentials', $user, $rawAttemptedEmail);

            throw ValidationException::withMessages(['email' => 'Email atau password salah.']);
        }
        if (! $user->is_active) {
            $this->recordLoginAttempt($request, LoginLog::EVENT_LOGIN_FAILED, 'inactive_account', $user, $rawAttemptedEmail);
            throw ValidationException::withMessages(['email' => 'Akun Anda dinonaktifkan. Hubungi admin.']);
        }
        if ($user->is_admin) {
            $this->recordLoginAttempt($request, LoginLog::EVENT_LOGIN_FAILED, 'invalid_credentials', $user, $rawAttemptedEmail);
            throw ValidationException::withMessages(['email' => 'Akun admin — silakan masuk via /admin/login.']);
        }

        RateLimiter::clear($ipKey);
        RateLimiter::clear($accountKey);
        Auth::login($user, (bool) ($data['remember'] ?? false));
        $user->forceFill(['last_activity_at' => now()])->save();
        $request->session()->regenerate();
        $this->recordLoginAttempt($request, LoginLog::EVENT_LOGIN, null, $user, $rawAttemptedEmail);

        return redirect()->intended(route('portal.dashboard'));
    }

    public function logout(Request $request)
    {
        if ($request->user()) {
            $this->recordLoginAttempt($request, LoginLog::EVENT_LOGOUT, null, $request->user(), $request->user()->email);
        }
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('portal.login')->with('success', 'Anda telah keluar.');
    }

    public function verifyEmail(Request $request, int $id, string $hash)
    {
        if (! $request->hasValidSignature()) {
            return redirect()->route('portal.login')->with('error', 'Link verifikasi tidak valid atau kedaluwarsa. Masuk lalu minta kirim ulang.');
        }
        $user = User::findOrFail($id);
        if (sha1($user->email) !== $hash) {
            abort(403);
        }
        // Link dibuka tanpa login -> arahkan login dulu (bukan dashboard
        // yang memantul ke login admin).
        if (! auth()->check()) {
            return redirect()->guest(route('portal.login'))->with('info', 'Silakan masuk. Setelah berhasil, verifikasi email akan dilanjutkan otomatis.');
        }
        // Mencegah akun A memverifikasi akun B secara tidak sengaja.
        if ((int) auth()->id() !== (int) $user->id) {
            return redirect()->route('portal.dashboard')->with('error', 'Link ini milik akun lain. Masuk dengan akun yang sesuai.');
        }
        if ($user->hasVerifiedEmail()) {
            return redirect()->route('portal.dashboard')->with('info', 'Email sudah terverifikasi sebelumnya.');
        }
        $user->markEmailAsVerified();

        return redirect()->route('portal.dashboard')->with('success', 'Email terverifikasi. Anda dapat melanjutkan pendaftaran.');
    }

    public function resendVerification(Request $request)
    {
        $user = $request->user();
        if ($user->hasVerifiedEmail()) {
            return back()->with('info', 'Email sudah terverifikasi.');
        }
        $verifyUrl = URL::temporarySignedRoute('portal.verification.verify', now()->addHours(24), ['id' => $user->id, 'hash' => sha1($user->email)]);
        MailService::send('verify_email', $user->email, 'Verifikasi Email PPDB SMK Tahfizh Al-Fatih', [
            'headline' => 'Verifikasi Email Anda',
            'body' => '<p>Klik tombol di bawah untuk memverifikasi email Anda.</p>',
            'cta' => 'Verifikasi Email',
            'cta_url' => $verifyUrl,
        ]);

        return back()->with('success', 'Link verifikasi dikirim ulang ke email Anda.');
    }

    public function showForgot()
    {
        return view('portal.auth.forgot')->with('title', 'Lupa Password');
    }

    public function sendReset(Request $request)
    {
        $request->validate(['email' => ['required', 'email', 'max:150']]);
        Password::sendResetLink($request->only('email'));

        // Respons selalu sama agar keberadaan akun tidak dapat ditebak.
        return back()->with('success', 'Jika email terdaftar, link reset password akan dikirim.');
    }

    public function showReset(string $token)
    {
        return view('portal.auth.reset', ['token' => $token, 'email' => request('email')])->with('title', 'Reset Password');
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required', 'email' => 'required|email',
            'password' => ['required', 'confirmed', PasswordRule::min(8)->letters()->numbers()],
        ]);
        $status = Password::reset($request->only('email', 'password', 'password_confirmation', 'token'), function ($user, $password) {
            $user->forceFill(['password' => Hash::make($password)])
                ->setRememberToken(Str::random(60));
            $user->save();
            event(new PasswordReset($user));
        });

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('portal.login')->with('success', 'Password berhasil direset. Silakan masuk.')
            : back()->with('error', 'Token reset tidak valid atau kedaluwarsa.');
    }

    private function recordLoginAttempt(Request $request, string $event, ?string $reason = null, ?User $user = null, ?string $attemptedEmail = null): void
    {
        $attemptedEmail ??= trim((string) $request->input('email'));
        $attemptedEmail = preg_replace('/[\x00-\x1F\x7F]/u', '', $attemptedEmail) ?? '';
        $normalizedEmail = strtolower($attemptedEmail);
        $user ??= filter_var($normalizedEmail, FILTER_VALIDATE_EMAIL)
            ? User::where('email', $normalizedEmail)->first()
            : null;

        LoginLog::create([
            'user_id' => $user?->id,
            'event' => $event,
            'channel' => 'portal',
            'attempted_email' => Str::limit($attemptedEmail, 255, ''),
            'failure_reason' => $reason,
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 1000, ''),
            'created_at' => now(),
        ]);
    }
}
