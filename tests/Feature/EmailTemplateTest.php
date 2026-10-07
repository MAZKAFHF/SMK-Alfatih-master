<?php

namespace Tests\Feature;

use App\Mail\PpdbMail;
use App\Models\PPDBRegistration;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
