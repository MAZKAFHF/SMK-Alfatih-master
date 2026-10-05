<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InterviewAppointment;
use App\Models\InterviewAssessment;
use App\Models\InterviewSlot;
use App\Models\PpdbPeriod;
use App\Models\RescheduleRequest;
use App\Services\AuditService;
use Illuminate\Http\Request;

class InterviewSlotController extends Controller
{
    public function index(Request $request)
    {
        // Default = periode dashboard (aktif → riwayat). Terima `period`/`period_id`.
        $ctx = \App\Services\PpdbContext::resolveFromRequest($request);
        $defaultPeriodId = (! $request->filled('period') && ! $request->filled('period_id')) ? $ctx['period']?->id : null;
        $periodFilter = $request->filled('period_id') ? $request->integer('period_id')
            : ($request->filled('period') ? $request->integer('period') : $defaultPeriodId);

        $slots = InterviewSlot::withCount('appointments')
            ->when($periodFilter, fn ($q) => $q->where('period_id', $periodFilter))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('date'), fn ($q) => $q->whereDate('date', $request->string('date')->toString()))
            ->orderBy('date')->orderBy('start_time')->paginate(15)->withQueryString();
        $today = InterviewAppointment::with(['application.program', 'slot'])
            ->whereHas('slot', fn ($q) => $q->when($periodFilter, fn ($qq) => $qq->where('period_id', $periodFilter))->whereDate('date', today('Asia/Jakarta')))
            ->orderBy('id')->get();
        $periods = PpdbPeriod::orderByDesc('id')->get(['id', 'academic_year', 'status']);
        $activePeriodId = $periodFilter;

        return view('admin.interview-slots.index', compact('slots', 'today', 'periods', 'activePeriodId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'location' => ['nullable', 'string', 'max:200'],
            'capacity' => ['required', 'integer', 'min:1', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);
        $data['period_id'] = PpdbPeriod::active()?->id;
        $data['status'] = 'active';
        InterviewSlot::create($data);
        AuditService::log('ppdb_slot_create', null, null, $data);

        return back()->with('success', 'Slot wawancara ditambahkan.');
    }

    public function toggle(InterviewSlot $slot)
    {
        $slot->update(['status' => $slot->status === 'active' ? 'inactive' : 'active']);

        return back()->with('success', 'Status slot diperbarui.');
    }

    public function destroy(InterviewSlot $slot)
    {
        if ($slot->appointments()->exists()) {
            return back()->with('error', 'Slot yang sudah memiliki booking tidak dapat dihapus. Nonaktifkan saja.');
        }
        $slot->delete();

        return back()->with('success', 'Slot dihapus.');
    }

    public function complete(Request $request, InterviewAppointment $appointment)
    {
        $data = $request->validate([
            'attendance' => ['required', 'in:attended,no_show'],
            'interview_notes' => ['nullable', 'string', 'max:3000'],
            'tahfizh_notes' => ['nullable', 'string', 'max:3000'],
            'tahsin_notes' => ['nullable', 'string', 'max:3000'],
            'recommendation' => ['nullable', 'string', 'max:500'],
        ]);
        $actorId = auth()->id();
        $appointment->loadMissing('application.period');
        if ($appointment->application->period?->isLockedForOperations()) {
            return back()->with('error', 'Periode PPDB sudah ditandai selesai. Penilaian wawancara dikunci.');
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($data, $appointment, $actorId) {
            $from = $appointment->application->application_status->value;

            $appointment->update([
                'status' => $data['attendance'] === 'attended' ? 'attended' : 'no_show',
                'attended_at' => $data['attendance'] === 'attended' ? now() : null,
            ]);
            InterviewAssessment::updateOrCreate(['appointment_id' => $appointment->id], [
                'attendance' => $data['attendance'], 'interview_notes' => $data['interview_notes'] ?? null,
                'tahfizh_notes' => $data['tahfizh_notes'] ?? null, 'tahsin_notes' => $data['tahsin_notes'] ?? null,
                'recommendation' => $data['recommendation'] ?? null, 'assessor_id' => $actorId, 'assessed_at' => now(),
            ]);
            $app = $appointment->application->fresh();
            if ($data['attendance'] === 'attended') {
                // Durable dalam SATU transaksi: assessment + status.
                // Request berikutnya (termasuk decision) langsung mengenalinya
                // tanpa perlu save kedua.
                $app->update(['application_status' => \App\Enums\ApplicationStatus::WaitingDecision, 'status' => 'pending']);
                \App\Models\StatusHistory::create(['application_id' => $app->id, 'from_status' => $from, 'to_status' => 'waiting_decision', 'actor_id' => $actorId, 'note' => 'Wawancara selesai']);
            }
        });

        $app = $appointment->application->fresh();
        AuditService::log('ppdb_interview_complete', $app);

        return back()->with('success', 'Kehadiran wawancara dicatat.');
    }

    public function decideReschedule(Request $request, RescheduleRequest $reschedule)
    {
        $data = $request->validate(['decision' => ['required', 'in:approved,rejected'], 'admin_note' => ['nullable', 'string', 'max:1000']]);
        $appt = $reschedule->appointment;
        if ($data['decision'] === 'approved') {
            // Pindah atomik ke slot usulan: applicant tidak pernah kehilangan janji.
            try {
                \Illuminate\Support\Facades\DB::transaction(function () use ($reschedule, $appt) {
                    $new = \App\Models\InterviewSlot::whereKey($reschedule->new_slot_id)->lockForUpdate()->firstOrFail();
                    // Locking the slot row serializes capacity changes. Keep
                    // COUNT free of FOR UPDATE for PostgreSQL compatibility.
                    $booked = \App\Models\InterviewAppointment::where('slot_id', $new->id)->count();
                    if ($new->status !== 'active' || $booked >= $new->capacity) {
                        throw \Illuminate\Validation\ValidationException::withMessages(['slot' => 'Slot pengganti sudah penuh. Minta pendaftar mengusulkan slot lain.']);
                    }
                    $old = $appt->slot;
                    $appt->update(['slot_id' => $new->id]);
                    $old->decrement('booked_count');
                    $new->increment('booked_count');
                });
            } catch (\Illuminate\Validation\ValidationException $e) {
                return back()->with('error', $e->getMessage());
            }
            $reschedule->update(['status' => 'approved', 'admin_note' => $data['admin_note'] ?? null, 'decided_by' => auth()->id(), 'decided_at' => now()]);
            \App\Services\NotificationService::notify($appt->application->applicant_account_id, $appt->application, 'Reschedule disetujui', 'Jadwal baru: '.$appt->fresh()->slot->date->format('d M Y').' pukul '.$appt->fresh()->slot->start_time.'.');
        } else {
            $reschedule->update(['status' => 'rejected', 'admin_note' => $data['admin_note'] ?? null, 'decided_by' => auth()->id(), 'decided_at' => now()]);
            \App\Services\NotificationService::notify($appt->application->applicant_account_id, $appt->application, 'Reschedule ditolak', (string) ($data['admin_note'] ?? 'Jadwal lama tetap berlaku.'));
        }

        return back()->with('success', 'Keputusan reschedule disimpan.');
    }
}
