<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\InterviewSlot;
use App\Models\PPDBRegistration;
use App\Models\RescheduleRequest;
use App\Services\AuditService;
use App\Services\MailService;
use App\Services\NotificationService;
use App\Services\SlotBookingService;
use Illuminate\Http\Request;

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
        $appt = $application->appointment;
        abort_unless($appt, 404);
        $data = $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
            'new_slot_id' => ['required', 'exists:interview_slots,id'],
        ]);
        if ((int) $data['new_slot_id'] === (int) $appt->slot_id) {
            return back()->with('error', 'Slot pengganti harus berbeda dengan jadwal saat ini.');
        }
        RescheduleRequest::create([
            'appointment_id' => $appt->id, 'old_slot_id' => $appt->slot_id,
            'new_slot_id' => $data['new_slot_id'],
            'reason' => $data['reason'], 'status' => 'pending',
        ]);
        NotificationService::notify($application->applicant_account_id, $application, 'Permohonan ubah jadwal dikirim', 'Menunggu review admin. Jadwal lama tetap berlaku sampai disetujui.');

        return back()->with('success', 'Permohonan reschedule dikirim. Jadwal lama Anda tetap berlaku sampai admin menyetujui.');
    }
}
