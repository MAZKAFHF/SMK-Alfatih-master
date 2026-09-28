<?php

namespace App\Services;

use App\Mail\PpdbMail;
use App\Models\EmailLog;
use App\Models\PPDBRegistration;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Bisnis tidak pernah rollback karena email gagal.
 * Setiap kirim dicatat di email_logs; gagal → status failed + retry via resend.
 */
class MailService
{
    public static function send(string $template, string $to, string $subject, array $data = [], ?PPDBRegistration $app = null): EmailLog
    {
        $log = EmailLog::create([
            'template' => $template,
            'recipient' => $to,
            'subject' => $subject,
            'payload' => $data,
            'application_id' => $app?->id,
            'status' => 'pending',
        ]);

        try {
            Mail::to($to)->send(new PpdbMail($subject, $template, $data, $app));
            $log->update(['status' => 'sent', 'sent_at' => now()]);
        } catch (\Throwable $e) {
            Log::warning('PPDB mail failed', ['template' => $template, 'to' => $to, 'error' => $e->getMessage()]);
            $log->update(['status' => 'failed', 'error' => substr($e->getMessage(), 0, 1000)]);
        }

        return $log->fresh();
    }

    public static function resend(EmailLog $log): EmailLog
    {
        $log->increment('retries');

        try {
            $app = $log->application;
            Mail::to($log->recipient)->send(new PpdbMail($log->subject ?? 'Informasi PPDB', $log->template, $log->payload ?? [], $app));
            $log->update(['status' => 'sent', 'sent_at' => now(), 'error' => null]);
        } catch (\Throwable $e) {
            $log->update(['status' => 'failed', 'error' => substr($e->getMessage(), 0, 1000)]);
        }

        return $log->fresh();
    }
}
