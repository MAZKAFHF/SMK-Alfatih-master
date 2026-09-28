<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Models\PPDBRegistration;
use App\Models\PpdbPeriod;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentPreviewActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_detail_uses_professional_action_not_tiny_link(): void
    {
        Storage::fake('ppdb_private');
        $admin = User::factory()->create(['is_admin' => true, 'is_superadmin' => true, 'is_active' => true]);
        $user = User::factory()->create(['is_admin' => false, 'is_applicant' => true, 'is_active' => true]);
        $period = PpdbPeriod::first() ?? PpdbPeriod::create(['academic_year' => '2026/2027', 'is_open' => true]);
        $program = Program::factory()->create(['status' => 'active']);

        $app = PPDBRegistration::create(array_merge(
            PpdbFinalSubmissionTest::completeData($program->id),
            ['applicant_account_id' => $user->id, 'period_id' => $period->id, 'source' => 'applicant', 'application_status' => ApplicationStatus::Submitted, 'status' => 'pending']
        ));
        \App\Services\DocumentService::ensurePlaceholders($app);
        $file = UploadedFile::fake()->create('akta.pdf', 200, 'application/pdf');
        \App\Services\DocumentService::store($app, \App\Enums\DocumentType::Akta, $file, $user->id);

        $page = $this->actingAs($admin)->get(route('admin.registrations.show', $app))->assertOk();
        // Label baru yang manusiawi.
        $page->assertSee('Lihat Berkas', false);
        // Link lemah lama hilang.
        $page->assertDontSee('Pratinjau privat', false);
        // Aksesibilitas: aria-label jelas, target blank aman.
        $page->assertSee('aria-label="Lihat berkas', false);
        $page->assertSee('target="_blank"', false);
        $page->assertSee('rel="noopener noreferrer"', false);
        // Metadata terbaca: nama file + versi.
        $page->assertSee('akta.pdf', false);
        // Tidak bocor private path mentah sebagai label utama.
        $page->assertDontSee('period-', false);
    }

    public function test_component_covers_all_document_types_consistently(): void
    {
        $component = file_get_contents(base_path('resources/views/components/admin/document-review-item.blade.php'));
        $this->assertStringContainsString('Lihat Berkas', $component);
        $this->assertStringNotContainsString('Pratinjau privat', $component);
        $this->assertStringContainsString('admin.documents.preview', $component);
        $this->assertStringContainsString('min-height: 44px', $component, 'Click target harus nyaman.');
        $this->assertStringContainsString('aria-label', $component);
        // Light/dark memakai token ctl, bukan warna hardcode lemah.
        $this->assertStringContainsString('var(--ctl-', $component);

        // Show memakai komponen reusable, bukan hardcode per kartu.
        $show = file_get_contents(base_path('resources/views/admin/registrations/show.blade.php'));
        $this->assertStringContainsString('document-review-item', $show);
        $this->assertStringNotContainsString('Pratinjau privat', $show);
    }
}
