<?php

namespace Tests\Feature;

use App\Models\EmailLog;
use App\Services\MailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Resend\WebhookSignature;
use Tests\TestCase;

class ResendIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_production_worker_consumes_the_dedicated_email_queue(): void
    {
        $compose = file_get_contents(base_path('docker-compose.production.yml'));

        $this->assertIsString($compose);
        $this->assertStringContainsString('--queue=emails,default', $compose);
    }

    public function test_local_mail_delivery_is_logged_without_real_network_request(): void
    {
        Mail::fake();

        $log = MailService::send(
            'verify_email',
            'pendaftar@example.test',
            'Verifikasi Email',
            ['headline' => 'Verifikasi', 'body' => '<p>Uji lokal.</p>'],
        );

        $this->assertSame('sent', $log->status);
        $this->assertSame('array', $log->provider);
        $this->assertNotNull($log->sent_at);
    }

    public function test_signed_resend_webhook_updates_delivery_status_and_is_idempotent(): void
    {
        $secretBytes = 'resend-webhook-test-secret';
        config(['services.resend.webhook_secret' => 'whsec_'.base64_encode($secretBytes)]);

        $log = EmailLog::create([
            'template' => 'application_submitted',
            'recipient' => 'pendaftar@example.test',
            'subject' => 'Pendaftaran diterima',
            'provider' => 'resend',
            'provider_message_id' => 'email_123',
            'status' => 'sent',
            'sent_at' => now()->subMinute(),
        ]);

        $payload = json_encode([
            'type' => 'email.delivered',
            'created_at' => now()->toIso8601String(),
            'data' => ['email_id' => 'email_123'],
        ], JSON_THROW_ON_ERROR);
        $headers = $this->signedHeaders('msg_123', $payload, $secretBytes);
        $this->assertTrue(WebhookSignature::verify($payload, [
            'svix-id' => $headers['HTTP_SVIX_ID'],
            'svix-timestamp' => $headers['HTTP_SVIX_TIMESTAMP'],
            'svix-signature' => $headers['HTTP_SVIX_SIGNATURE'],
        ], 'whsec_'.base64_encode($secretBytes)));

        $this->call('POST', route('webhooks.resend'), [], [], [], $headers, $payload)
            ->assertOk()
            ->assertJson(['received' => true, 'duplicate' => false]);

        $this->call('POST', route('webhooks.resend'), [], [], [], $headers, $payload)
            ->assertOk()
            ->assertJson(['received' => true, 'duplicate' => true]);

        $log->refresh();
        $this->assertSame('delivered', $log->status);
        $this->assertNotNull($log->delivered_at);
        $this->assertDatabaseCount('email_webhook_events', 1);
    }

    public function test_utc_webhook_time_is_stored_without_timezone_drift(): void
    {
        $secretBytes = 'resend-webhook-test-secret';
        config(['services.resend.webhook_secret' => 'whsec_'.base64_encode($secretBytes)]);

        $log = EmailLog::create([
            'template' => 'verify_email',
            'recipient' => 'pendaftar@example.test',
            'provider' => 'resend',
            'provider_message_id' => 'email_timezone_test',
            'status' => 'sent',
        ]);
        $payload = json_encode([
            'type' => 'email.delivered',
            'created_at' => '2026-10-07T04:10:18.000Z',
            'data' => ['email_id' => 'email_timezone_test'],
        ], JSON_THROW_ON_ERROR);
        $headers = $this->signedHeaders('msg_timezone_test', $payload, $secretBytes);

        $this->call('POST', route('webhooks.resend'), [], [], [], $headers, $payload)->assertOk();

        $this->assertSame('2026-10-07T04:10:18+00:00', $log->fresh()->delivered_at->utc()->toIso8601String());
    }

    public function test_resend_webhook_rejects_invalid_signature(): void
    {
        config(['services.resend.webhook_secret' => 'whsec_'.base64_encode('secret')]);

        $this->withHeaders([
            'svix-id' => 'msg_invalid',
            'svix-timestamp' => (string) time(),
            'svix-signature' => 'v1,invalid',
        ])->postJson(route('webhooks.resend'), [
            'type' => 'email.delivered',
            'data' => ['email_id' => 'email_123'],
        ])->assertStatus(400);
    }

    private function signedHeaders(string $messageId, string $payload, string $secret): array
    {
        $timestamp = (string) time();
        $signature = base64_encode(hash_hmac(
            'sha256',
            $messageId.'.'.$timestamp.'.'.$payload,
            $secret,
            true,
        ));

        return [
            'HTTP_SVIX_ID' => $messageId,
            'HTTP_SVIX_TIMESTAMP' => $timestamp,
            'HTTP_SVIX_SIGNATURE' => 'v1,'.$signature,
            'CONTENT_TYPE' => 'application/json',
        ];
    }
}
