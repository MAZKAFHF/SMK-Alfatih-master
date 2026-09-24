<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePPDBRegistrationRequest;
use App\Models\PPDBRegistration;
use App\Models\PpdbSetting;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class PPDBController extends Controller
{
    public function index()
    {
        $ppdb = PpdbSetting::current();

        return view('public.ppdb.index', compact('ppdb'))
            ->with('title', 'PPDB Online');
    }

    public function siswa()
    {
        $ppdb = PpdbSetting::current();
        if (! $ppdb->isOpen()) {
            return view('public.ppdb.closed', compact('ppdb'))->with('title', 'PPDB Ditutup');
        }
        $programs = Program::active()->orderBy('order')->get();

        return view('public.ppdb.siswa', compact('programs', 'ppdb'))
            ->with('title', 'Daftar PPDB');
    }

    public function store(StorePPDBRegistrationRequest $request)
    {
        $ppdb = PpdbSetting::current();
        if (! $ppdb->isOpen()) {
            return back()->withInput()->with('error', 'Pendaftaran PPDB sedang ditutup ('.$ppdb->statusLabel().'). Silakan hubungi panitia.');
        }

        // Double-submit / duplicate protection: check recent duplicate (same phone+name within 2min)
        $validated = $request->validated();
        $recentDup = PPDBRegistration::where('name', $validated['name'])
            ->where('phone', $validated['phone'] ?? null)
            ->where('program_id', $validated['program_id'])
            ->where('created_at', '>', now()->subMinutes(2))
            ->exists();
        if ($recentDup) {
            return back()->withInput()->with('error', 'Pendaftaran duplikat terdeteksi — Anda baru saja mengirim data serupa. Silakan tunggu beberapa menit atau cek status pendaftaran.');
        }

        $attempts = 0;
        $registration = null;
        while ($attempts < 5) {
            try {
                $registration = PPDBRegistration::create($validated);
                break;
            } catch (\Illuminate\Database\QueryException $e) {
                $msg = $e->getMessage();
                if (str_contains($msg, 'UNIQUE') || str_contains($msg, 'unique') || str_contains($msg, 'database is locked') || str_contains($msg, 'busy')) {
                    $attempts++;
                    usleep(100000 * $attempts + random_int(0, 50000));
                    // regenerate number on next loop by clearing it so booted will generate anew
                    $validated['registration_number'] = null;
                    continue;
                }
                Log::error('PPDB store failed (query)', ['error' => $msg]);
                return back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan data pendaftaran. Silakan coba lagi.');
            } catch (\Throwable $e) {
                Log::error('PPDB store failed', ['error' => $e->getMessage()]);
                return back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan data pendaftaran. Silakan coba lagi.');
            }
        }

        if (! $registration) {
            return back()->withInput()->with('error', 'Gagal menyimpan pendaftaran setelah beberapa percobaan. Silakan coba lagi.');
        }

        // Attempt to send email if available — failure should not lose DB
        if (! empty($registration->email)) {
            try {
                Log::info('PPDB new registration notification would send to '.$registration->email, ['reg' => $registration->registration_number]);
            } catch (\Throwable $mailEx) {
                Log::warning('PPDB email notification failed', ['error' => $mailEx->getMessage()]);
            }
        }

        return redirect()
            ->route('ppdb.status', ['registration_number' => $registration->registration_number])
            ->with('success', 'Pendaftaran Anda berhasil dikirim. Simpan nomor pendaftaran berikut untuk memantau status. Nomor: '.$registration->registration_number.' — Gunakan tanggal lahir untuk cek status demi keamanan.');
    }

    public function status(Request $request)
    {
        // Rate limit status checks: 10 per minute per IP
        $key = 'ppdb-status|'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 10)) {
            $seconds = RateLimiter::availableIn($key);

            return view('public.ppdb.status', [
                'registration' => null,
                'registrationNumber' => $request->string('registration_number')->toString(),
                'error' => "Terlalu banyak percobaan. Coba lagi dalam {$seconds} detik.",
                'birthDate' => $request->string('birth_date')->toString(),
            ])->with('title', 'Cek Status Pendaftaran');
        }
        RateLimiter::hit($key, 60);

        $registrationNumber = $request->string('registration_number')->trim()->toString();
        $birthDate = $request->string('birth_date')->trim()->toString();

        $registration = null;
        if ($registrationNumber !== '' && $birthDate !== '') {
            $registration = PPDBRegistration::query()->with('program')
                ->where('registration_number', $registrationNumber)
                ->whereDate('birth_date', $birthDate)
                ->first();
        } elseif ($registrationNumber !== '' && $request->has('birth_date')) {
            // birth_date provided but empty or mismatch -> show not found with privacy message
            $registration = null;
        } elseif ($registrationNumber !== '') {
            // Legacy fallback: allow check with only regnum but show limited data warning? For privacy we require birth_date
            // Keep behavior: if birth_date param missing entirely, try without it but flag privacy notice
            $registration = PPDBRegistration::query()->with('program')->where('registration_number', $registrationNumber)->first();
            // If found, we still require birth_date for full details — handled in view
        }

        return view('public.ppdb.status', [
            'registration' => $registration,
            'registrationNumber' => $registrationNumber,
            'birthDate' => $birthDate,
        ])->with('title', 'Cek Status Pendaftaran');
    }
}
