<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ApplicationStatus;
use App\Enums\InterviewAppointmentStatus;
use App\Http\Controllers\Controller;
use App\Models\InterviewAppointment;
use App\Models\InterviewAssessment;
use App\Models\InterviewSlot;
use App\Models\PpdbPeriod;
use App\Models\RescheduleRequest;
use App\Models\StatusHistory;
use App\Services\AuditService;
use App\Services\NotificationService;
use App\Services\PpdbContext;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InterviewSlotController extends Controller
{
    public function index(Request $request)
    {
        // Default = periode dashboard (aktif → riwayat). Terima `period`/`period_id`.
        $ctx = PpdbContext::resolveFromRequest($request);
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
            'period_id' => ['required', 'exists:ppdb_periods,id'],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'location' => ['nullable', 'string', 'max:200'],
            'capacity' => ['required', 'integer', 'min:1', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);
        $period = PpdbPeriod::findOrFail($data['period_id']);
        if ($period->isLockedForOperations()) {
            return back()->withInput()->withErrors(['period_id' => 'Periode sudah selesai. Slot baru tidak dapat ditambahkan.']);
        }
        $data['status'] = 'active';
        InterviewSlot::create($data);
        AuditService::log('ppdb_slot_create', null, null, $data);

        return back()->with('success', 'Slot wawancara ditambahkan.');
    }

    public function toggle(InterviewSlot $slot)
    {
        if ($slot->period?->isLockedForOperations()) {
            return back()->with('error', 'Periode sudah selesai. Status slot dikunci.');
        }
        if ($slot->status !== 'active') {
            $slotStart = Carbon::parse($slot->date->toDateString().' '.$slot->start_time, 'Asia/Jakarta');
            if ($slotStart->isPast() || $slot->appointments()->count() >= $slot->capacity) {
                return back()->with('error', 'Slot yang sudah lewat atau penuh tidak dapat diaktifkan kembali.');
            }
        }
        $slot->update(['status' => $slot->status === 'active' ? 'inactive' : 'active']);

        return back()->with('success', 'Status slot diperbarui.');
    }

    public function destroy(InterviewSlot $slot)
    {
        if ($slot->period?->isLockedForOperations()) {
            return back()->with('error', 'Periode sudah selesai. Slot dikunci sebagai riwayat.');
        }
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
        try {
            DB::transaction(function () use ($data, $appointment, $actorId) {
                $appointment = InterviewAppointment::whereKey($appointment->id)->lockForUpdate()->firstOrFail();
                $appointment->loadMissing(['application.period', 'slot']);
                $app = $appointment->application;
                if ($app->period?->isLockedForOperations()) {
                    throw ValidationException::withMessages(['attendance' => 'Periode PPDB sudah selesai. Penilaian wawancara dikunci.']);
                }
                if ((int) $appointment->slot?->period_id !== (int) $app->period_id) {
                    throw ValidationException::withMessages(['attendance' => 'Jadwal tidak sesuai dengan periode pendaftaran.']);
                }

                $target = $data['attendance'] === 'attended' ? InterviewAppointmentStatus::Attended : InterviewAppointmentStatus::NoShow;
                $current = $appointment->status;
                if ($current !== InterviewAppointmentStatus::Scheduled && $current !== $target) {
                    throw ValidationException::withMessages(['attendance' => 'Kehadiran final tidak dapat diubah ke status yang berlawanan.']);
                }
                if ($current === InterviewAppointmentStatus::Scheduled && $app->application_status !== ApplicationStatus::Scheduled) {
                    throw ValidationException::withMessages(['attendance' => 'Aplikasi tidak berada pada tahap wawancara terjadwal.']);
                }
                $slotStart = Carbon::parse($appointment->slot->date->toDateString().' '.$appointment->slot->start_time, 'Asia/Jakarta');
                if ($current === InterviewAppointmentStatus::Scheduled && $slotStart->isFuture()) {
                    throw ValidationException::withMessages(['attendance' => 'Kehadiran baru dapat dicatat setelah waktu wawancara dimulai.']);
                }

                $from = $app->application_status->value;
                $appointment->update([
                    'status' => $target->value,
                    'attended_at' => $target === InterviewAppointmentStatus::Attended ? ($appointment->attended_at ?? now()) : null,
                ]);
                InterviewAssessment::updateOrCreate(['appointment_id' => $appointment->id], [
                    'attendance' => $data['attendance'], 'interview_notes' => $data['interview_notes'] ?? null,
                    'tahfizh_notes' => $data['tahfizh_notes'] ?? null, 'tahsin_notes' => $data['tahsin_notes'] ?? null,
                    'recommendation' => $data['recommendation'] ?? null, 'assessor_id' => $actorId, 'assessed_at' => now(),
                ]);
                $app = $app->fresh();
                if ($target === InterviewAppointmentStatus::Attended && $current === InterviewAppointmentStatus::Scheduled) {
                    // Durable dalam SATU transaksi: assessment + status.
                    $app->update(['application_status' => ApplicationStatus::WaitingDecision, 'status' => 'pending']);
                    StatusHistory::create(['application_id' => $app->id, 'from_status' => $from, 'to_status' => 'waiting_decision', 'actor_id' => $actorId, 'note' => 'Wawancara selesai']);
                }
            });
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        $app = $appointment->application->fresh();
        AuditService::log('ppdb_interview_complete', $app);

        return back()->with('success', 'Kehadiran wawancara dicatat.');
    }

    public function decideReschedule(Request $request, RescheduleRequest $reschedule)
    {
        $data = $request->validate(['decision' => ['required', 'in:approved,rejected'], 'admin_note' => ['nullable', 'string', 'max:1000']]);
        try {
            $appt = DB::transaction(function () use ($reschedule, $data) {
                $reschedule = RescheduleRequest::whereKey($reschedule->id)->lockForUpdate()->firstOrFail();
                if ($reschedule->status !== 'pending') {
                    throw ValidationException::withMessages(['decision' => 'Permohonan ini sudah diputuskan dan tidak dapat diproses ulang.']);
                }
                $appt = InterviewAppointment::whereKey($reschedule->appointment_id)->lockForUpdate()->firstOrFail();
                $appt->loadMissing(['application.period', 'slot']);
                if ($appt->application->period?->isLockedForOperations()) {
                    throw ValidationException::withMessages(['decision' => 'Periode PPDB sudah selesai. Reschedule dikunci.']);
                }
                if ($appt->status !== InterviewAppointmentStatus::Scheduled || $appt->application->application_status !== ApplicationStatus::Scheduled) {
                    throw ValidationException::withMessages(['decision' => 'Wawancara tidak lagi berada pada tahap yang dapat dijadwalkan ulang.']);
                }

                if ($data['decision'] === 'approved') {
                    $new = InterviewSlot::whereKey($reschedule->new_slot_id)->lockForUpdate()->firstOrFail();
                    // Locking the slot row serializes capacity changes. Keep
                    // COUNT free of FOR UPDATE for PostgreSQL compatibility.
                    $booked = InterviewAppointment::where('slot_id', $new->id)->count();
                    if ((int) $new->period_id !== (int) $appt->application->period_id) {
                        throw ValidationException::withMessages(['decision' => 'Slot pengganti berasal dari periode PPDB yang berbeda.']);
                    }
                    $newStart = Carbon::parse($new->date->toDateString().' '.$new->start_time, 'Asia/Jakarta');
                    if ((int) $new->id === (int) $appt->slot_id || $new->status !== 'active' || $booked >= $new->capacity || $newStart->isPast()) {
                        throw ValidationException::withMessages(['slot' => 'Slot pengganti sudah penuh. Minta pendaftar mengusulkan slot lain.']);
                    }
                    $old = $appt->slot;
                    $appt->update(['slot_id' => $new->id]);
                    $old->update(['booked_count' => $old->appointments()->count()]);
                    $new->update(['booked_count' => $new->appointments()->count()]);
                    $reschedule->update(['status' => 'approved', 'admin_note' => $data['admin_note'] ?? null, 'decided_by' => auth()->id(), 'decided_at' => now()]);
                } else {
                    $reschedule->update(['status' => 'rejected', 'admin_note' => $data['admin_note'] ?? null, 'decided_by' => auth()->id(), 'decided_at' => now()]);
                }

                return $appt->fresh(['application', 'slot']);
            });
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        if ($data['decision'] === 'approved') {
            NotificationService::notify($appt->application->applicant_account_id, $appt->application, 'Reschedule disetujui', 'Jadwal baru: '.$appt->fresh()->slot->date->format('d M Y').' pukul '.$appt->fresh()->slot->start_time.'.');
        } else {
            NotificationService::notify($appt->application->applicant_account_id, $appt->application, 'Reschedule ditolak', (string) ($data['admin_note'] ?? 'Jadwal lama tetap berlaku.'));
        }

        return back()->with('success', 'Keputusan reschedule disimpan.');
    }
}
