<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ApplicationStatus;
use App\Http\Controllers\Controller;
use App\Models\EmailLog;
use App\Models\PpdbPeriod;
use App\Models\PPDBRegistration;
use App\Models\Program;
use App\Models\RescheduleRequest;
use App\Models\StatusHistory;
use App\Services\AdminCodeService;
use App\Services\AuditService;
use App\Services\DocumentService;
use App\Services\PpdbAvailability;
use App\Services\PpdbContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RegistrationController extends Controller
{
    public function index(Request $request)
    {
        // Default = periode dashboard (aktif → riwayat terakhir), bukan semua tahun.
        $ctx = PpdbContext::resolveFromRequest($request);
        $defaultPeriodId = (! $request->filled('period') && ! $request->filled('period_id')) ? $ctx['period']?->id : null;
        $periodFilter = $request->filled('period_id') ? $request->integer('period_id')
            : ($request->filled('period') ? $request->integer('period') : $defaultPeriodId);

        $registrations = PPDBRegistration::query()
            ->with(['program', 'period', 'documents', 'appointment.slot', 'decision'])
            ->when($request->filled('application_status'), fn ($q) => $q->where('application_status', $request->string('application_status')))
            ->when($request->filled('program_id'), fn ($q) => $q->where('program_id', $request->integer('program_id')))
            ->when($periodFilter, fn ($q) => $q->where('period_id', $periodFilter))
            ->when($request->filled('source'), fn ($q) => $q->where('source', $request->string('source')))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');

                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('registration_number', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('nik', 'like', "%{$search}%")
                        ->orWhere('nisn', 'like', "%{$search}%")
                        ->orWhere('parent_name', 'like', "%{$search}%")
                        ->orWhere('father_name', 'like', "%{$search}%")
                        ->orWhere('mother_name', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $programs = Program::orderBy('name')->get(['id', 'name']);
        $periods = PpdbPeriod::orderByDesc('id')->get(['id', 'academic_year', 'status']);
        // Ringkasan di-scope periode yang sedang dilihat.
        $scopeCounts = fn ($q) => $periodFilter ? $q->where('period_id', $periodFilter) : $q;
        $counts = [
            'total' => $scopeCounts(PPDBRegistration::query())->count(),
            'submitted' => $scopeCounts(PPDBRegistration::query()->where('application_status', 'submitted'))->count(),
            'needs_revision' => $scopeCounts(PPDBRegistration::query()->where('application_status', 'needs_revision'))->count(),
            'verified' => $scopeCounts(PPDBRegistration::query()->whereIn('application_status', ['verified', 'waiting_slot', 'scheduled']))->count(),
            'waiting_decision' => $scopeCounts(PPDBRegistration::query()->whereIn('application_status', ['interviewed', 'waiting_decision']))->count(),
        ];
        $activePeriodId = $periodFilter;
        $isHistoryView = $periodFilter && PpdbPeriod::find($periodFilter)?->isHistory();

        return view('admin.registrations.index', compact('registrations', 'programs', 'periods', 'counts', 'activePeriodId', 'isHistoryView'));
    }

    public function show(PPDBRegistration $registration)
    {
        $registration->load(['program', 'period', 'account', 'documents.revisions', 'appointment.slot', 'appointment.assessment', 'decision', 'history.actor', 'notes.author']);

        $emailLog = EmailLog::where('application_id', $registration->id)->latest()->first();
        $reschedules = RescheduleRequest::whereHas('appointment', fn ($q) => $q->where('application_id', $registration->id))->with(['oldSlot', 'newSlot'])->latest()->get();

        return view('admin.registrations.show', compact('registration', 'emailLog', 'reschedules'));
    }

    public function print(PPDBRegistration $registration)
    {
        $registration->load(['program', 'period', 'documents', 'appointment.slot', 'decision']);

        return view('admin.registrations.print', compact('registration'));
    }

    /** Cetak daftar (filter-aware): layout print khusus, tanpa sidebar/nav admin. */
    public function printList(Request $request)
    {
        $ctx = PpdbContext::resolveFromRequest($request);
        $defaultPeriodId = (! $request->filled('period') && ! $request->filled('period_id')) ? $ctx['period']?->id : null;
        $periodFilter = $request->filled('period_id') ? $request->integer('period_id')
            : ($request->filled('period') ? $request->integer('period') : $defaultPeriodId);

        $registrations = PPDBRegistration::query()->with(['program', 'period', 'appointment.slot', 'decision'])
            ->when($request->filled('application_status'), fn ($q) => $q->where('application_status', $request->string('application_status')))
            ->when($request->filled('program_id'), fn ($q) => $q->where('program_id', $request->integer('program_id')))
            ->when($periodFilter, fn ($q) => $q->where('period_id', $periodFilter))
            ->when($request->filled('search'), fn ($q) => $q->where(fn ($qq) => $qq->where('name', 'like', "%{$request->string('search')}%")->orWhere('registration_number', 'like', "%{$request->string('search')}%")))
            ->orderBy('created_at')
            ->limit(500)
            ->get();

        $periodObj = $periodFilter ? PpdbPeriod::find($periodFilter) : null;
        $filters = collect([
            'Periode' => $periodObj?->academic_year,
            'Alur' => $request->filled('application_status') ? ApplicationStatus::tryFrom($request->string('application_status')->toString())?->label() : null,
            'Program' => $request->filled('program_id') ? Program::find($request->integer('program_id'))?->name : null,
        ])->filter();

        return view('admin.registrations.print-list', compact('registrations', 'filters'));
    }

    /** Entri manual admin — boleh saat publik ditutup, tetap validasi + audit + source jelas. */
    public function create()
    {
        $programs = Program::active()->orderBy('order')->get();
        $periods = PpdbPeriod::orderByDesc('id')->get();

        return view('admin.registrations.create', compact('programs', 'periods'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'gender' => ['required', 'in:laki-laki,perempuan'],
            'program_id' => ['required', 'exists:programs,id'],
            'period_id' => ['required', 'exists:ppdb_periods,id'],
            'nisn' => ['nullable', 'string', 'max:20'],
            'birth_place' => ['nullable', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:150'],
            'school_origin' => ['nullable', 'string', 'max:150'],
            'father_name' => ['nullable', 'string', 'max:150'],
            'mother_name' => ['nullable', 'string', 'max:150'],
            'override_reason' => ['nullable', 'string', 'max:500'],
        ]);
        $period = PpdbPeriod::find($data['period_id']);
        // Entri manual melewati aturan publik, tetapi bila periode
        // TUTUP/PENUH wajib ada alasan override eksplisit + audit.
        $state = PpdbAvailability::forPeriod($period);
        if (! $state->canRegister() && blank($data['override_reason'] ?? null)) {
            return back()->withInput()->withErrors([
                'override_reason' => 'Periode '.$state->publicLabel().' — tulis alasan override untuk entri manual pengecualian.',
            ]);
        }
        $data['academic_year'] = $period->academic_year;
        $data['source'] = 'admin_manual';
        $data['created_by'] = auth()->id();
        $data['application_status'] = ApplicationStatus::Submitted->value;
        $data['status'] = 'pending';
        $data['submitted_at'] = now();
        $overrideReason = $data['override_reason'] ?? null;
        unset($data['override_reason']);

        $app = PPDBRegistration::create($data);
        DocumentService::ensurePlaceholders($app);
        StatusHistory::create(['application_id' => $app->id, 'from_status' => null, 'to_status' => 'submitted', 'actor_id' => auth()->id(), 'note' => 'Entri manual admin'.($overrideReason ? ' (override: '.$overrideReason.')' : '')]);
        AuditService::log('ppdb_manual_create', $app, null, ['override_reason' => $overrideReason, 'period_state' => $state->status]);

        return redirect()->route('admin.registrations.show', $app)->with('success', 'Pendaftaran manual dibuat: '.$app->registration_number);
    }

    public function destroy(Request $request, PPDBRegistration $registration)
    {
        AdminCodeService::verify($request);

        try {
            $registration->delete();
            AuditService::log('ppdb_delete', $registration);

            return redirect()->route('admin.registrations.index')
                ->with('success', 'Data pendaftaran dipindahkan ke Trash.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menghapus data pendaftaran. Silakan coba lagi.');
        }
    }

    public function trash(Request $request)
    {
        $registrations = PPDBRegistration::onlyTrashed()
            ->with('program')
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->string('search');
                $q->where(fn ($qq) => $qq->where('name', 'like', "%{$search}%")->orWhere('registration_number', 'like', "%{$search}%"));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.registrations.trash', compact('registrations'));
    }

    public function restore(int $id)
    {
        $registration = PPDBRegistration::onlyTrashed()->findOrFail($id);
        $registration->restore();
        AuditService::log('ppdb_restore', $registration);

        return back()->with('success', "Pendaftaran {$registration->registration_number} dipulihkan.");
    }

    public function forceDelete(Request $request, int $id)
    {
        if (! auth()->user()?->is_superadmin) {
            abort(403);
        }
        AdminCodeService::verify($request);
        $registration = PPDBRegistration::onlyTrashed()->findOrFail($id);
        DocumentService::deleteAllFor($registration);
        if ($registration->photo_path) {
            Storage::disk('public')->delete($registration->photo_path);
        }
        $label = $registration->registration_number;
        $registration->forceDelete();
        AuditService::log('ppdb_force_delete', null, null, ['registration_number' => $label]);

        return back()->with('success', "Pendaftaran {$label} dihapus permanen.");
    }

    public function export(Request $request)
    {
        // Default = periode dashboard (aktif → riwayat). Terima `period` maupun `period_id`.
        $ctx = PpdbContext::resolveFromRequest($request);
        $defaultPeriodId = (! $request->filled('period') && ! $request->filled('period_id')) ? $ctx['period']?->id : null;
        $periodFilter = $request->filled('period_id') ? $request->integer('period_id')
            : ($request->filled('period') ? $request->integer('period') : $defaultPeriodId);

        $query = PPDBRegistration::query()->with(['program', 'period', 'appointment.slot', 'decision'])
            ->when($request->filled('application_status'), fn ($q) => $q->where('application_status', $request->string('application_status')))
            ->when($request->filled('program_id'), fn ($q) => $q->where('program_id', $request->integer('program_id')))
            ->when($periodFilter, fn ($q) => $q->where('period_id', $periodFilter))
            ->when($request->filled('academic_year'), fn ($q) => $q->where('academic_year', $request->string('academic_year')))
            ->when($request->filled('search'), fn ($q) => $q->where(fn ($qq) => $qq->where('name', 'like', "%{$request->string('search')}%")->orWhere('registration_number', 'like', "%{$request->string('search')}%")))
            ->orderBy('created_at');

        $periodTag = $periodFilter ? PpdbPeriod::find($periodFilter)?->academic_year ?? '' : '';
        $filename = 'ppdb-'.preg_replace('/[^0-9A-Za-z]+/', '', (string) $periodTag).'-'.now('Asia/Jakarta')->format('Ymd_His').'.csv';

        AuditService::log('ppdb_export', null, null, ['filters' => $request->query(), 'count' => $query->count()]);

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $columns = ['Nomor Registrasi', 'Nama', 'NIK', 'NISN', 'Jenis Kelamin', 'Tempat Lahir', 'Tanggal Lahir', 'Alamat', 'Asal Sekolah', 'HP', 'Email', 'Ayah', 'Ibu', 'Program', 'Status Alur', 'Tgl Wawancara', 'Hasil', 'Tahun Ajaran', 'Tanggal Daftar (WIB)'];

        return response()->stream(function () use ($query, $columns) {
            $out = fopen('php://output', 'w');
            // BOM for Excel UTF-8
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, $columns);
            $query->chunk(500, function ($rows) use ($out) {
                foreach ($rows as $r) {
                    fputcsv($out, [
                        $r->registration_number,
                        $r->name,
                        $r->nik,
                        $r->nisn,
                        $r->gender,
                        $r->birth_place,
                        $r->birth_date?->format('Y-m-d'),
                        $r->address,
                        $r->school_origin,
                        $r->phone,
                        $r->email,
                        $r->father_name ?? $r->parent_name,
                        $r->mother_name,
                        $r->program?->name,
                        $r->application_status->label(),
                        $r->appointment?->slot ? $r->appointment->slot->date->format('Y-m-d').' '.$r->appointment->slot->start_time : '',
                        $r->decision?->result->label() ?? '',
                        $r->academic_year,
                        $r->created_at->setTimezone('Asia/Jakarta')->format('Y-m-d H:i'),
                    ]);
                }
            });
            fclose($out);
        }, 200, $headers);
    }
}
