<?php

namespace App\Http\Controllers\Portal;

use App\Enums\ApplicationStatus;
use App\Enums\DocumentType;
use App\Http\Controllers\Controller;
use App\Models\PPDBRegistration;
use App\Models\PpdbPeriod;
use App\Models\Program;
use App\Models\StatusHistory;
use App\Services\AuditService;
use App\Services\ApplicationCreationUnavailableException;
use App\Services\DocumentService;
use App\Services\MailService;
use App\Services\NotificationService;
use App\Services\PpdbAvailability;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ApplicationController extends Controller
{
    public function create()
    {
        $state = PpdbAvailability::resolveForApplicationCreation();
        if (! $state->canCreateApplication()) {
            return redirect()->route('portal.dashboard')->with('error', $state->creationBlockedMessage());
        }

        $programs = Program::active()->orderBy('order')->get();
        $period = $state->period;

        return view('portal.applications.create', compact('programs', 'period'));
    }

    public function store(Request $request)
    {
        // Guard sebelum validasi memberi respons profesional untuk direct POST,
        // lalu createApplication() memeriksa ulang di bawah lock (stale page).
        $state = PpdbAvailability::resolveForApplicationCreation();
        if (! $state->canCreateApplication()) {
            return redirect()->route('portal.dashboard')->with('error', $state->creationBlockedMessage());
        }
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'nik' => ['nullable', 'string', 'max:25'],
            'nisn' => ['nullable', 'string', 'max:20'],
            'birth_place' => ['nullable', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'gender' => ['required', 'in:laki-laki,perempuan'],
            'address' => ['nullable', 'string', 'max:500'],
            'province' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'village' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'school_origin' => ['nullable', 'string', 'max:150'],
            'school_npsn' => ['nullable', 'string', 'max:30'],
            'graduation_year' => ['nullable', 'string', 'max:9'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:150'],
            'father_name' => ['nullable', 'string', 'max:150'],
            'father_phone' => ['nullable', 'string', 'max:30'],
            'father_occupation' => ['nullable', 'string', 'max:100'],
            'mother_name' => ['nullable', 'string', 'max:150'],
            'mother_phone' => ['nullable', 'string', 'max:30'],
            'mother_occupation' => ['nullable', 'string', 'max:100'],
            'guardian_name' => ['nullable', 'string', 'max:150'],
            'guardian_phone' => ['nullable', 'string', 'max:30'],
            'guardian_relation' => ['nullable', 'string', 'max:50'],
            'parent_name' => ['nullable', 'string', 'max:150'],
            'program_id' => ['required', 'integer', Rule::exists('programs', 'id')],
        ]);

        try {
            $app = PpdbAvailability::createApplication(function (PpdbPeriod $period) use ($data, $request) {
                // Cegah duplikat tak sengaja DALAM AKUN yang sama (nama+tgl lahir
                // sama dalam 1 periode). Nama sama antar keluarga tetap sah.
                $dup = PPDBRegistration::where('period_id', $period->id)
                    ->where('applicant_account_id', $request->user()->id)
                    ->where('name', $data['name'])
                    ->when(! empty($data['birth_date']), fn ($q) => $q->whereDate('birth_date', $data['birth_date']))
                    ->whereNull('deleted_at')->exists();
                if ($dup) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'name' => 'Siswa ini tampaknya sudah memiliki pendaftaran pada periode ini. Periksa daftar aplikasi Anda.',
                    ]);
                }

                $payload = array_merge($data, [
                    'applicant_account_id' => $request->user()->id,
                    'period_id' => $period->id,
                    'academic_year' => $period->academic_year,
                    'application_status' => ApplicationStatus::Draft->value,
                    'status' => 'pending',
                    'source' => 'applicant',
                ]);

                $application = PPDBRegistration::create($payload);
                DocumentService::ensurePlaceholders($application);
                StatusHistory::create(['application_id' => $application->id, 'from_status' => null, 'to_status' => 'draft', 'actor_id' => $request->user()->id, 'note' => 'Aplikasi dibuat']);
                AuditService::log('portal_application_create', $application);

                return $application;
            });
        } catch (ApplicationCreationUnavailableException $e) {
            return redirect()->route('portal.dashboard')->with('error', $e->getMessage());
        }

        return redirect()->route('portal.applications.show', $app)->with('success', 'Data siswa dibuat sebagai draf. Lengkapi dokumen lalu kirim final.');
    }

    public function show(Request $request, PPDBRegistration $application)
    {
        $this->authorize('view', $application);
        $application->load(['program', 'period', 'documents', 'appointment.slot', 'decision', 'history.actor', 'notes.author']);
        $availableSlots = \App\Models\InterviewSlot::available()
            ->when($application->period_id, fn ($q) => $q->where('period_id', $application->period_id))
            ->when($application->appointment, fn ($q) => $q->where('id', '!=', $application->appointment->slot_id))
            ->get();

        $nextAction = $application->nextPortalAction();
        $stage = $request->string('tahap')->toString();
        if (! in_array($stage, ['data', 'dokumen', 'verifikasi', 'wawancara', 'hasil'], true)) {
            $stage = $nextAction['stage'];
        }

        return view('portal.applications.show', compact('application', 'availableSlots', 'nextAction', 'stage'));
    }

    public function edit(Request $request, PPDBRegistration $application)
    {
        $this->authorize('update', $application);
        $programs = Program::active()->orderBy('order')->get();

        return view('portal.applications.edit', compact('application', 'programs'));
    }

    public function update(Request $request, PPDBRegistration $application)
    {
        $this->authorize('update', $application);
        // DRAF: sengaja longgar — pemohon boleh simpan pekerjaan sebagian.
        // Kelengkapan penuh ditegakkan server-side di submit() via FinalSubmissionCheck.
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'nik' => ['nullable', 'string', 'max:25'],
            'nisn' => ['nullable', 'string', 'max:20'],
            'birth_place' => ['nullable', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'gender' => ['required', 'in:laki-laki,perempuan'],
            'address' => ['nullable', 'string', 'max:500'],
            'province' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'village' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'school_origin' => ['nullable', 'string', 'max:150'],
            'school_npsn' => ['nullable', 'string', 'max:30'],
            'graduation_year' => ['nullable', 'string', 'max:9'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:150'],
            'father_name' => ['nullable', 'string', 'max:150'],
            'father_phone' => ['nullable', 'string', 'max:30'],
            'father_occupation' => ['nullable', 'string', 'max:100'],
            'mother_name' => ['nullable', 'string', 'max:150'],
            'mother_phone' => ['nullable', 'string', 'max:30'],
            'mother_occupation' => ['nullable', 'string', 'max:100'],
            'guardian_name' => ['nullable', 'string', 'max:150'],
            'guardian_phone' => ['nullable', 'string', 'max:30'],
            'guardian_relation' => ['nullable', 'string', 'max:50'],
            'program_id' => ['required', 'integer', Rule::exists('programs', 'id')],
        ]);
        $application->update($data);

        return redirect()->route('portal.applications.show', $application)->with('success', 'Draf berhasil disimpan.');
    }

    public function review(Request $request, PPDBRegistration $application)
    {
        $this->authorize('view', $application);
        $application->load(['program', 'documents', 'period']);
        $gate = \App\Services\FinalSubmissionCheck::check($application);

        return view('portal.applications.review', compact('application', 'gate'));
    }

    public function submit(Request $request, PPDBRegistration $application)
    {
        $this->authorize('view', $application);
        if ($application->application_status !== ApplicationStatus::Draft && $application->application_status !== ApplicationStatus::NeedsRevision) {
            return back()->with('error', 'Hanya draf atau perlu-perbaikan yang dapat dikirim.');
        }
        // Validasi form dulu (pesan inline ID), lalu gerbang verifikasi email.
        $request->validate(
            ['confirm' => ['required', 'accepted']],
            ['confirm.accepted' => 'Centang pernyataan ini sebelum mengirim pendaftaran.', 'confirm.required' => 'Centang pernyataan ini sebelum mengirim pendaftaran.'],
            ['confirm' => 'Pernyataan persetujuan']
        );
        if (! $request->user()->hasVerifiedEmail()) {
            return back()->withInput()->withErrors(['email' => 'Verifikasi email terlebih dahulu sebelum kirim final. Periksa kotak masuk email Anda.'])->with('error', 'Verifikasi email terlebih dahulu sebelum kirim final. Periksa kotak masuk email Anda.');
        }

        // GERBANG FINAL: seluruh data wajib + dokumen diperiksa server-side.
        // HTML `required` saja TIDAK cukup — request langsung pun ditolak di sini.
        $gate = \App\Services\FinalSubmissionCheck::check($application);
        if (! $gate['valid']) {
            $sections = implode(', ', array_keys($gate['missing_sections']));

            return back()
                ->withErrors($gate['errors'])
                ->with('error', 'Pendaftaran belum dapat dikirim. Lengkapi bagian yang masih diperlukan: '.$sections.'.');
        }

        // Transaksi atomik: kunci periode -> cek jendela + kuota -> submit.
        // Mencegah 101/100 dan submit melewati tenggat.
        try {
            \App\Services\PpdbAvailability::submitApplication($application, $request->user()->id);
        } catch (\App\Services\QuotaFullException $e) {
            return back()->with('error', $e->getMessage());
        }

        $app = $application->fresh();
        NotificationService::notify($app->applicant_account_id, $app, 'Pendaftaran berhasil dikirim', 'Nomor: '.$app->registration_number.'. Pantau verifikasi di portal.', route('portal.applications.show', $app));
        if ($request->user()->email) {
            MailService::send('application_submitted', $request->user()->email, 'Pendaftaran PPDB Terkirim — '.$app->registration_number, [
                'headline' => 'Pendaftaran Terkirim',
                'preheader' => 'Nomor pendaftaran '.$app->registration_number,
                'body' => '<p>Pendaftaran <strong>'.e($app->name).'</strong> ('.e($app->registration_number).') berhasil dikirim dan menunggu verifikasi panitia.</p>',
                'cta' => 'Lihat Pendaftaran', 'cta_url' => route('portal.applications.show', $app),
            ], $app);
        }

        return redirect()->route('portal.applications.show', $app)->with('success', 'Pendaftaran dikirim. Nomor: '.$app->registration_number);
    }
}
