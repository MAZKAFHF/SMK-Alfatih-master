<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Models\InterviewAppointment;
use App\Models\InterviewSlot;
use App\Models\PPDBRegistration;
use App\Models\StatusHistory;
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
            if ($app->period?->isLockedForOperations()) {
                throw ValidationException::withMessages(['slot' => 'Periode PPDB sudah ditandai selesai. Pemilihan jadwal dikunci.']);
            }
            if ($app->appointment) {
                throw ValidationException::withMessages(['slot' => 'Aplikasi ini sudah memiliki jadwal wawancara.']);
            }
            if (! in_array($app->application_status, [ApplicationStatus::Verified, ApplicationStatus::WaitingSlot], true)) {
                throw ValidationException::withMessages(['slot' => 'Aplikasi belum terverifikasi untuk memilih jadwal.']);
            }

            /** @var InterviewSlot $slot */
            $slot = InterviewSlot::whereKey($slotId)->lockForUpdate()->firstOrFail();
            // The slot row serializes bookings. PostgreSQL rejects FOR UPDATE
            // on aggregate COUNT queries, so the count itself stays unlocked.
            $booked = InterviewAppointment::where('slot_id', $slot->id)->count();

            if ($slot->status !== 'active' || $booked >= $slot->capacity) {
                throw ValidationException::withMessages(['slot' => 'Slot sudah penuh. Silakan pilih jadwal lain.']);
            }
            $slotStart = \Carbon\Carbon::parse($slot->date->toDateString().' '.$slot->start_time, 'Asia/Jakarta');
            if ($slotStart->isPast()) {
                throw ValidationException::withMessages(['slot' => 'Slot sudah lewat. Pilih jadwal mendatang.']);
            }

            $appt = InterviewAppointment::create([
                'application_id' => $app->id,
                'slot_id' => $slot->id,
                'status' => 'scheduled',
                'booked_at' => now(),
            ]);
            $slot->increment('booked_count');

            $from = $app->application_status->value;
            $app->update(['application_status' => ApplicationStatus::Scheduled, 'status' => ApplicationStatus::Scheduled->toLegacy()]);
            StatusHistory::create([
                'application_id' => $app->id, 'from_status' => $from,
                'to_status' => 'scheduled', 'actor_id' => $actorId ?? $app->applicant_account_id,
                'note' => 'Booking slot '.$slot->date->format('d M Y').' '.$slot->start_time,
            ]);
            NotificationService::notify($app->applicant_account_id, $app, 'Jadwal wawancara dikonfirmasi', 'Slot '.$slot->date->format('d M Y').' pukul '.$slot->start_time.'.', route('portal.applications.show', $app));

            return $appt;
        });
    }
}
