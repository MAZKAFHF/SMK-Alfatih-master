<?php

namespace App\Http\Controllers\Portal;

use App\Enums\ApplicationStatus;
use App\Enums\InterviewAppointmentStatus;
use App\Http\Controllers\Controller;
use App\Models\InterviewAppointment;
use App\Models\InterviewSlot;
use App\Models\PPDBRegistration;
use App\Models\RescheduleRequest;
use App\Services\AuditService;
use App\Services\MailService;
use App\Services\NotificationService;
use App\Services\SlotBookingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InterviewController extends Controller
{
    public function slots(Request $request, PPDBRegistration $application)
    {
        $this->authorize('view', $application);
        $slots = InterviewSlot::available()
            ->when($application->period_id, fn ($q) => $q->where('period_id', $application->period_id))
            ->get();

        return view('portal.applications.slots', compact('application', 'slots'));
    }

    public function book(Request $request, PPDBRegistration $application)
    {
        $this->authorize('view', $application);
        $data = $request->validate(['slot_id' => ['required', 'exists:interview_slots,id']]);
        $appt = SlotBookingService::book($application, (int) $data['slot_id'], $request->user()->id);
        AuditService::log('portal_slot_book', $application);

        if ($request->user()->email) {
            MailService::send('interview_confirmed', $request->user()->email, 'Jadwal Wawancara Dikonfirmasi — '.$application->registration_number, [
                'headline' => 'Jadwal Wawancara Dikonfirmasi',
                'body' => '<p>Jadwal: <strong>'.$appt->slot->date->format('d M Y').' pukul '.$appt->slot->start_time.'</strong><br>Lokasi: '.e($appt->slot->location ?? '-').'</p>',
                'cta' => 'Lihat Janji', 'cta_url' => route('portal.applications.show', $application),
            ], $application);
        }

        return redirect()->route('portal.applications.show', $application)->with('success', 'Jadwal wawancara berhasil dipesan.');
    }

    public function requestReschedule(Request $request, PPDBRegistration $application)
    {
        $this->authorize('view', $application);
        $data = $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
            'new_slot_id' => ['required', 'exists:interview_slots,id'],
        ]);
        try {
            DB::transaction(function () use ($application, $data) {
                $app = PPDBRegistration::whereKey($application->id)->lockForUpdate()->firstOrFail();
                if ($app->period?->isLockedForOperations()) {
                    throw ValidationException::withMessages(['new_slot_id' => 'Periode PPDB sudah selesai. Perubahan jadwal dikunci.']);
                }
                if ($app->application_status !== ApplicationStatus::Scheduled) {
                    throw ValidationException::withMessages(['new_slot_id' => 'Jadwal hanya dapat diubah ketika wawancara masih berstatus terjadwal.']);
                }

                $appt = InterviewAppointment::where('application_id', $app->id)->lockForUpdate()->first();
                if (! $appt || $appt->status !== InterviewAppointmentStatus::Scheduled) {
                    throw ValidationException::withMessages(['new_slot_id' => 'Janji wawancara tidak lagi dapat dijadwalkan ulang.']);
                }
                if ((int) $data['new_slot_id'] === (int) $appt->slot_id) {
                    throw ValidationException::withMessages(['new_slot_id' => 'Slot pengganti harus berbeda dengan jadwal saat ini.']);
                }
                if (RescheduleRequest::where('appointment_id', $appt->id)->where('status', 'pending')->exists()) {
                    throw ValidationException::withMessages(['new_slot_id' => 'Masih ada permohonan ubah jadwal yang menunggu keputusan admin.']);
                }

                $new = InterviewSlot::whereKey($data['new_slot_id'])->lockForUpdate()->firstOrFail();
                if ((int) $new->period_id !== (int) $app->period_id) {
                    throw ValidationException::withMessages(['new_slot_id' => 'Slot pengganti harus berasal dari periode PPDB yang sama.']);
                }
                $booked = InterviewAppointment::where('slot_id', $new->id)->count();
                $newStart = Carbon::parse($new->date->toDateString().' '.$new->start_time, 'Asia/Jakarta');
                if ($new->status !== 'active' || $booked >= $new->capacity || $newStart->isPast()) {
                    throw ValidationException::withMessages(['new_slot_id' => 'Slot pengganti sudah tidak tersedia. Pilih jadwal mendatang lainnya.']);
                }

                RescheduleRequest::create([
                    'appointment_id' => $appt->id, 'old_slot_id' => $appt->slot_id,
                    'new_slot_id' => $new->id,
                    'reason' => $data['reason'], 'status' => 'pending',
                ]);
            });
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }
        NotificationService::notify($application->applicant_account_id, $application, 'Permohonan ubah jadwal dikirim', 'Menunggu review admin. Jadwal lama tetap berlaku sampai disetujui.');

        return back()->with('success', 'Permohonan reschedule dikirim. Jadwal lama Anda tetap berlaku sampai admin menyetujui.');
    }
}
