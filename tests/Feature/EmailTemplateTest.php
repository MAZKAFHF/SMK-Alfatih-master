<?php

namespace Tests\Feature;

use App\Mail\PpdbMail;
use App\Models\EmailLog;
use App\Models\PPDBRegistration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmailTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_ppdb_email_has_professional_responsive_and_secure_content(): void
    {
        $application = PPDBRegistration::factory()->create([
            'name' => 'Calon Siswa',
            'registration_number' => 'PPDB-2026-00999',
        ]);

        $html = (new PpdbMail(
            'Pendaftaran diterima',
            'application_submitted',
            [
                'headline' => 'Pendaftaran Terkirim',
                'preheader' => 'Nomor pendaftaran Anda sudah tersedia.',
                'body' => '<p>Data Anda sudah diterima panitia.</p>',
                'cta' => 'Lihat Pendaftaran',
                'cta_url' => route('portal.applications.show', $application),
            ],
            $application,
        ))->render();

        $this->assertStringContainsString('PORTAL PENERIMAAN SISWA BARU', $html);
        $this->assertStringContainsString('PPDB-2026-00999', $html);
        $this->assertStringContainsString('Jika tombol tidak berfungsi', $html);
        $this->assertStringContainsString('Panitia tidak pernah meminta password', $html);
        $this->assertStringContainsString('@media only screen and (max-width: 640px)', $html);
        $this->assertStringNotContainsString('ALFATIH//FUTURE', $html);
    }

    public function test_applicant_password_reset_uses_branded_auditable_email(): void
    {
        Mail::fake();
        $user = User::factory()->create([
            'is_admin' => false,
            'is_applicant' => true,
        ]);

        $user->sendPasswordResetNotification('secure-test-token');

        $log = EmailLog::latest('id')->firstOrFail();
        $this->assertSame('reset_password', $log->template);
        $this->assertSame($user->email, $log->recipient);
        $this->assertStringContainsString('/portal/reset-password/secure-test-token', $log->payload['cta_url']);
        $this->assertStringContainsString('email='.urlencode($user->email), $log->payload['cta_url']);
    }

    public function test_admin_password_reset_targets_admin_reset_screen(): void
    {
        Mail::fake();
        $user = User::factory()->create([
            'is_admin' => true,
            'is_applicant' => false,
        ]);

        $user->sendPasswordResetNotification('admin-test-token');

        $log = EmailLog::latest('id')->firstOrFail();
        $this->assertStringContainsString('/admin/reset-password/admin-test-token', $log->payload['cta_url']);
    }
}
