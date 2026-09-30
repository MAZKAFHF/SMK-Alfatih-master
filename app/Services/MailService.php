<?php

namespace App\Services;

use App\Jobs\SendPpdbMail;
use App\Models\EmailLog;
use App\Models\PPDBRegistration;

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
            'provider' => config('mail.default'),
            'status' => 'queued',
        ]);

        try {
            SendPpdbMail::dispatch($log->id);
        } catch (\Throwable $e) {
            $log->update(['status' => 'failed', 'error' => substr($e->getMessage(), 0, 1000)]);
        }

        return $log->fresh();
    }

    public static function resend(EmailLog $log): EmailLog
    {
        $log->increment('retries');
        $log->update([
            'status' => 'queued',
            'error' => null,
            'provider_message_id' => null,
            'sent_at' => null,
            'delivered_at' => null,
            'bounced_at' => null,
            'complained_at' => null,
            'last_event_at' => null,
        ]);

        try {
            SendPpdbMail::dispatch($log->id);
        } catch (\Throwable $e) {
            $log->update(['status' => 'failed', 'error' => substr($e->getMessage(), 0, 1000)]);
        }

        return $log->fresh();
    }
}
