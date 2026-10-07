<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Models\InterviewAppointment;
use App\Models\InterviewSlot;
use App\Models\PpdbPeriod;
use App\Models\PPDBRegistration;
use App\Models\StatusHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SlotBookingService
{
    /**
     * Transaction-safe booking. Hanya satu yang lolos saat race slot terakhir.
     *
     * @throws ValidationException (409: penuh / tidak valid)
     */
    public static function book(PPDBRegistration $app, int $slotId, ?int $actorId = null): InterviewAppointment
    {
        return DB::transaction(function () use ($app, $slotId, $actorId) {
            /** @var PPDBRegistration $lockedApp */
            $lockedApp = PPDBRegistration::whereKey($app->id)->lockForUpdate()->firstOrFail();
            $period = $lockedApp->period_id
                ? PpdbPeriod::whereKey($lockedApp->period_id)->lockForUpdate()->first()
                : null;

            if ($period?->isLockedForOperations()) {
                throw ValidationException::withMessages(['slot' => 'Periode PPDB sudah ditandai selesai. Pemilihan jadwal dikunci.']);
            }
            if (InterviewAppointment::where('application_id', $lockedApp->id)->exists()) {
                throw ValidationException::withMessages(['slot' => 'Aplikasi ini sudah memiliki jadwal wawancara.']);
            }
            if (! in_array($lockedApp->application_status, [ApplicationStatus::Verified, ApplicationStatus::WaitingSlot], true)) {
                throw ValidationException::withMessages(['slot' => 'Aplikasi belum terverifikasi untuk memilih jadwal.']);
            }

            /** @var InterviewSlot $slot */
            $slot = InterviewSlot::whereKey($slotId)->lockForUpdate()->firstOrFail();
            if (! $lockedApp->period_id || (int) $slot->period_id !== (int) $lockedApp->period_id) {
                throw ValidationException::withMessages(['slot' => 'Slot tidak tersedia untuk periode pendaftaran ini.']);
            }
            // The slot row serializes bookings. PostgreSQL rejects FOR UPDATE
            // on aggregate COUNT queries, so the count itself stays unlocked.
            $booked = InterviewAppointment::where('slot_id', $slot->id)->count();

            if ($slot->status !== 'active' || $booked >= $slot->capacity) {
                throw ValidationException::withMessages(['slot' => 'Slot sudah penuh. Silakan pilih jadwal lain.']);
            }
            $slotStart = Carbon::parse($slot->date->toDateString().' '.$slot->start_time, 'Asia/Jakarta');
            if ($slotStart->isPast()) {
                throw ValidationException::withMessages(['slot' => 'Slot sudah lewat. Pilih jadwal mendatang.']);
            }

            $appt = InterviewAppointment::create([
                'application_id' => $lockedApp->id,
                'slot_id' => $slot->id,
                'status' => 'scheduled',
                'booked_at' => now(),
            ]);
            $slot->increment('booked_count');

            $from = $lockedApp->application_status->value;
            $lockedApp->update(['application_status' => ApplicationStatus::Scheduled, 'status' => ApplicationStatus::Scheduled->toLegacy()]);
            StatusHistory::create([
                'application_id' => $lockedApp->id, 'from_status' => $from,
                'to_status' => 'scheduled', 'actor_id' => $actorId ?? $lockedApp->applicant_account_id,
                'note' => 'Booking slot '.$slot->date->format('d M Y').' '.$slot->start_time,
            ]);
            NotificationService::notify($lockedApp->applicant_account_id, $lockedApp, 'Jadwal wawancara dikonfirmasi', 'Slot '.$slot->date->format('d M Y').' pukul '.$slot->start_time.'.', route('portal.applications.show', $lockedApp));

            return $appt;
        });
    }
}
