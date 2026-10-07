<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ApplicationStatus;
use App\Enums\DecisionResult;
use App\Enums\DocumentStatus;
use App\Http\Controllers\Controller;
use App\Models\ApplicationDocument;
use App\Models\EmailLog;
use App\Models\InternalNote;
use App\Models\PPDBRegistration;
use App\Services\AuditService;
use App\Services\DecisionService;
use App\Services\MailService;
use App\Services\NotificationService;
use App\Services\VerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class PpdbWorkflowController extends Controller
{
    public function reviewDocument(Request $request, ApplicationDocument $document)
    {
        $document->loadMissing('application.period');
        $application = $document->application;
        if ($application->period?->isLockedForOperations()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Periode PPDB sudah selesai. Review dokumen dikunci sebagai riwayat.'], 422);
            }

            return back()->with('error', 'Periode PPDB sudah selesai. Review dokumen dikunci sebagai riwayat.');
        }
        if (! in_array($application->application_status, [
            ApplicationStatus::Submitted,
            ApplicationStatus::Resubmitted,
            ApplicationStatus::NeedsRevision,
        ], true)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Dokumen hanya dapat direview pada tahap verifikasi/perbaikan.'], 422);
            }

            return back()->with('error', 'Dokumen hanya dapat direview pada tahap verifikasi/perbaikan.');
        }
        if (! $document->path || ! Storage::disk($document->disk ?: 'ppdb_private')->exists($document->path)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Dokumen belum memiliki file yang dapat direview.'], 422);
            }

            return back()->with('error', 'Dokumen belum memiliki file yang dapat direview.');
        }
        $data = $request->validate([
            'status' => ['required', 'in:valid,needs_revision'],
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ]);
        if ($data['status'] === 'needs_revision' && blank($data['admin_note'])) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Catatan untuk pendaftar wajib diisi saat meminta perbaikan.', 'errors' => ['admin_note' => ['Catatan untuk pendaftar wajib diisi saat meminta perbaikan.']]], 422);
            }

            return back()->with('error', 'Catatan untuk pendaftar wajib diisi saat meminta perbaikan.');
        }
        $document->update([
            'status' => $data['status'],
            'admin_note' => $data['admin_note'] ?? null,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);
        AuditService::log('ppdb_doc_review', $document->application, null, ['type' => $document->type->value, 'status' => $data['status']]);

        if ($data['status'] === 'needs_revision' && $document->application->applicant_account_id) {
            NotificationService::notify($document->application->applicant_account_id, $document->application, 'Dokumen perlu diperbaiki: '.$document->type->label(), (string) $data['admin_note']);
        }

        $message = 'Dokumen '.$document->type->label().' ditandai: '.DocumentStatus::from($data['status'])->label().'.';
        if ($request->expectsJson()) {
            return response()->json(['message' => $message, 'status' => $data['status'], 'label' => DocumentStatus::from($data['status'])->label()]);
        }

        return back()->with('success', $message);
    }

    public function verify(Request $request, PPDBRegistration $registration)
    {
        $data = $request->validate(['override_reason' => ['nullable', 'string', 'max:1000']]);
        try {
            VerificationService::verify($registration, auth()->id(), filled($data['override_reason'] ?? null), $data['override_reason'] ?? null);
        } catch (ValidationException $e) {
            return back()->with('error', $e->getMessage());
        }
        AuditService::log('ppdb_verify', $registration);
        $this->mailSafe($registration, 'application_verified', 'Pendaftaran Terverifikasi — '.$registration->registration_number, 'Pendaftaran Terverifikasi', '<p>Pendaftaran <strong>'.e($registration->name).'</strong> telah terverifikasi. Silakan pilih jadwal wawancara.</p>');

        return back()->with('success', 'Aplikasi terverifikasi. Pendaftar dapat memilih jadwal.');
    }

    public function requestRevision(Request $request, PPDBRegistration $registration)
    {
        $data = $request->validate(['note' => ['required', 'string', 'max:2000']]);
        try {
            VerificationService::requestRevision($registration, auth()->id(), $data['note']);
        } catch (ValidationException $e) {
            return back()->with('error', $e->getMessage());
        }
        AuditService::log('ppdb_request_revision', $registration, null, ['note' => $data['note']]);
        $this->mailSafe($registration, 'correction_requested', 'Dokumen Perlu Diperbaiki — '.$registration->name, 'Perlu Perbaikan', '<p>'.nl2br(e($data['note'])).'</p>');

        return back()->with('success', 'Permintaan perbaikan dikirim ke pendaftar.');
    }

    public function decide(Request $request, PPDBRegistration $registration)
    {
        $data = $request->validate([
            'result' => ['required', 'in:passed,not_passed'],
            'internal_note' => ['nullable', 'string', 'max:2000'],
            'applicant_message' => ['nullable', 'string', 'max:2000'],
            'confirm' => ['required', 'accepted'],
        ], [], ['confirm' => 'Konfirmasi keputusan']);
        try {
            $decision = DecisionService::decide($registration, DecisionResult::from($data['result']), auth()->id(), $data['internal_note'] ?? null, $data['applicant_message'] ?? null);
        } catch (ValidationException $e) {
            return back()->with('error', $e->getMessage());
        }
        AuditService::log('ppdb_decide', $registration, null, ['result' => $data['result']]);

        return back()->with('success', 'Keputusan internal disimpan: '.$decision->result->label().'. Rilis untuk menampilkan ke pendaftar.');
    }

    public function release(Request $request, PPDBRegistration $registration)
    {
        $decision = $registration->decision;
        abort_unless($decision, 404);
        try {
            $releasedNow = DecisionService::release($decision, auth()->id());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }
        if (! $releasedNow) {
            return back()->with('success', 'Hasil ini sudah pernah dirilis. Tidak ada notifikasi atau email duplikat yang dikirim.');
        }
        AuditService::log('ppdb_release', $registration);
        $isPass = $decision->result === DecisionResult::Passed;
        $this->mailSafe($registration, $isPass ? 'result_pass' : 'result_not_pass', ($isPass ? 'Selamat — Hasil PPDB ' : 'Hasil PPDB ').$registration->registration_number, $isPass ? 'Anda Dinyatakan Lulus' : 'Hasil Seleksi PPDB', $isPass
            ? '<p>Selamat! Berdasarkan rangkaian PPDB, Anda dinyatakan <strong>lulus</strong>. Hubungi Admin PPDB via WhatsApp resmi untuk administrasi lanjutan.</p>'
            : '<p>Terima kasih telah mengikuti PPDB. Saat ini Anda <strong>belum dapat dinyatakan lulus</strong>. Hubungi Admin PPDB untuk informasi lebih lanjut.</p>');

        return back()->with('success', 'Hasil dirilis ke pendaftar + email dicoba kirim (gagal email tidak membatalkan keputusan).');
    }

    public function addNote(Request $request, PPDBRegistration $registration)
    {
        $data = $request->validate(['body' => ['required', 'string', 'max:2000']]);
        InternalNote::create(['application_id' => $registration->id, 'author_id' => auth()->id(), 'body' => $data['body']]);

        return back()->with('success', 'Catatan internal disimpan (tidak terlihat pendaftar).');
    }

    public function resendEmail(Request $request, PPDBRegistration $registration)
    {
        $log = EmailLog::where('application_id', $registration->id)->latest('id')->first();
        abort_unless($log, 404, 'Belum ada email untuk aplikasi ini.');
        MailService::resend($log);

        return back()->with('success', 'Percobaan kirim ulang dicatat ('.$log->fresh()->status.').');
    }

    private function mailSafe(PPDBRegistration $registration, string $template, string $subject, string $headline, string $body): void
    {
        $to = $registration->account?->email ?? $registration->email;
        if (! $to) {
            return;
        }
        $registration->loadMissing(['period', 'program']);
        MailService::send($template, $to, $subject, [
            'headline' => $headline, 'body' => $body,
            'cta' => $registration->applicant_account_id ? 'Buka Portal' : 'Hubungi Panitia',
            'cta_url' => $registration->applicant_account_id ? route('portal.applications.show', $registration) : route('contact.index'),
        ], $registration);
    }
}
