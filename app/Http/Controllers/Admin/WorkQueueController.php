<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\EmailLog;
use App\Models\InterviewAppointment;
use App\Models\PPDBRegistration;
use App\Models\RescheduleRequest;

class WorkQueueController extends Controller
{
    public function index()
    {
        $applications = PPDBRegistration::with(['period', 'program'])->whereIn('application_status', ['submitted', 'resubmitted'])->oldest('submitted_at')->limit(25)->get();
        $reschedules = RescheduleRequest::with(['appointment.application', 'oldSlot', 'newSlot'])->where('status', 'pending')->oldest()->limit(25)->get();
        $interviews = InterviewAppointment::with(['application', 'slot'])->where('status', 'scheduled')
            ->whereHas('slot', fn ($q) => $q->whereDate('date', '<=', today('Asia/Jakarta')))->limit(25)->get();
        $messages = ContactMessage::where('handling_status', '!=', 'resolved')->where('is_archived', false)->oldest()->limit(25)->get();
        $failedEmails = EmailLog::with('application')->where('status', 'failed')->latest()->limit(25)->get();

        return view('admin.work-queue.index', compact('applications', 'reschedules', 'interviews', 'messages', 'failedEmails'));
    }
}
