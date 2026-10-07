<?php

namespace App\Http\Controllers;

use App\Models\EmailLog;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Resend\WebhookSignature;
use Throwable;

class ResendWebhookController extends Controller
{
    private const STATUS_PRIORITY = [
        'queued' => 0,
        'sending' => 1,
        'retrying' => 1,
        'sent' => 2,
        'delivery_delayed' => 3,
        'delivered' => 4,
        'bounced' => 5,
        'complained' => 6,
        'failed' => 6,
    ];

    public function __invoke(Request $request): JsonResponse
    {
        if ((int) $request->server('CONTENT_LENGTH', 0) > 1_048_576) {
            return response()->json(['message' => 'Payload terlalu besar.'], 413);
        }

        $secret = (string) config('services.resend.webhook_secret');
        if ($secret === '') {
            return response()->json(['message' => 'Webhook belum dikonfigurasi.'], 503);
        }

        $headers = [
            'svix-id' => (string) $request->header('svix-id'),
            'svix-timestamp' => (string) $request->header('svix-timestamp'),
            'svix-signature' => (string) $request->header('svix-signature'),
        ];
        $rawPayload = $request->getContent();

        try {
            WebhookSignature::verify($rawPayload, $headers, $secret);
            $event = json_decode($rawPayload, true, 32, JSON_THROW_ON_ERROR);
        } catch (Throwable $e) {
            Log::warning('Resend webhook rejected', ['reason' => $e->getMessage()]);

            return response()->json(['message' => 'Signature webhook tidak valid.'], 400);
        }

        $eventId = $headers['svix-id'];
        $eventType = (string) ($event['type'] ?? '');
        $providerMessageId = (string) ($event['data']['email_id'] ?? '');
        if ($eventId === '' || $eventType === '' || $providerMessageId === '') {
            return response()->json(['message' => 'Payload webhook tidak lengkap.'], 422);
        }

        $occurredAt = $this->parseOccurredAt($event['created_at'] ?? $event['data']['created_at'] ?? null);
        $processed = false;

        DB::transaction(function () use ($eventId, $eventType, $providerMessageId, $occurredAt, &$processed): void {
            $inserted = DB::table('email_webhook_events')->insertOrIgnore([
                'provider' => 'resend',
                'event_id' => $eventId,
                'event_type' => $eventType,
                'provider_message_id' => $providerMessageId,
                'occurred_at' => $occurredAt,
                'processed_at' => now(),
            ]);

            if ($inserted === 0) {
                return;
            }

            $processed = true;
            $log = EmailLog::where('provider_message_id', $providerMessageId)->lockForUpdate()->first();
            if (! $log) {
                return;
            }

            $nextStatus = match ($eventType) {
                'email.sent' => 'sent',
                'email.delivery_delayed' => 'delivery_delayed',
                'email.delivered' => 'delivered',
                'email.bounced' => 'bounced',
                'email.complained' => 'complained',
                'email.failed' => 'failed',
                default => null,
            };

            $updates = ['last_event_at' => $this->latest($log->last_event_at, $occurredAt)];
            if ($nextStatus !== null && $this->mayAdvance($log->status, $nextStatus)) {
                $updates['status'] = $nextStatus;
            }

            match ($eventType) {
                'email.sent' => $updates['sent_at'] = $log->sent_at ?? $occurredAt,
                'email.delivered' => $updates['delivered_at'] = $occurredAt,
                'email.bounced' => $updates['bounced_at'] = $occurredAt,
                'email.complained' => $updates['complained_at'] = $occurredAt,
                default => null,
            };

            $log->update($updates);
        });

        return response()->json(['received' => true, 'duplicate' => ! $processed]);
    }

    private function mayAdvance(string $current, string $next): bool
    {
        return (self::STATUS_PRIORITY[$next] ?? 0) >= (self::STATUS_PRIORITY[$current] ?? 0);
    }

    private function parseOccurredAt(mixed $value): CarbonImmutable
    {
        try {
            return filled($value)
                ? CarbonImmutable::parse((string) $value)->setTimezone((string) config('app.timezone'))
                : now()->toImmutable();
        } catch (Throwable) {
            return now()->toImmutable();
        }
    }

    private function latest(mixed $current, CarbonImmutable $incoming): CarbonImmutable
    {
        return $current && $current->greaterThan($incoming) ? $current->toImmutable() : $incoming;
    }
}
