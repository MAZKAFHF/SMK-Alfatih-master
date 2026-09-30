<?php

namespace App\Jobs;

use App\Mail\PpdbMail;
use App\Models\EmailLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendPpdbMail implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $uniqueFor = 600;

    public function __construct(public int $emailLogId)
    {
        $this->onQueue('emails');
        $this->afterCommit();
    }

    public function uniqueId(): string
    {
        return (string) $this->emailLogId;
    }

    public function backoff(): array
    {
        return [60, 300];
    }

    public function handle(): void
    {
        $log = EmailLog::with('application')->find($this->emailLogId);

        if (! $log || in_array($log->status, ['delivered', 'bounced', 'complained'], true)) {
            return;
        }

        $log->update([
            'status' => 'sending',
            'provider' => config('mail.default'),
            'error' => null,
        ]);

        try {
            $message = Mail::to($log->recipient)->sendNow(new PpdbMail(
                $log->subject ?? 'Informasi PPDB',
                $log->template,
                $log->payload ?? [],
                $log->application,
            ));

            $providerMessageId = $message?->getOriginalMessage()
                ?->getHeaders()
                ?->get('X-Resend-Email-ID')
                ?->getBodyAsString();

            $log->update([
                'status' => 'sent',
                'provider_message_id' => $providerMessageId,
                'sent_at' => now(),
                'error' => null,
            ]);
        } catch (Throwable $e) {
            $log->update([
                'status' => $this->attempts() >= $this->tries ? 'failed' : 'retrying',
                'error' => substr($e->getMessage(), 0, 1000),
            ]);

            Log::warning('PPDB mail delivery failed', [
                'email_log_id' => $log->id,
                'template' => $log->template,
                'attempt' => $this->attempts(),
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function failed(?Throwable $exception): void
    {
        EmailLog::whereKey($this->emailLogId)->update([
            'status' => 'failed',
            'error' => substr($exception?->getMessage() ?? 'Pengiriman email gagal setelah beberapa percobaan.', 0, 1000),
        ]);
    }
}
