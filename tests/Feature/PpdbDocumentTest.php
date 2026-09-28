<?php

namespace Tests\Feature;

use App\Models\PPDBRegistration;
use App\Models\PpdbPeriod;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Regresi bug kritis: kartu dokumen berbagi target upload.
 * Setiap slot HARUS terikat stabil ke type-key, bukan urutan DOM.
 */
class PpdbDocumentTest extends TestCase
{
    use RefreshDatabase;

    private function applicant(): User
    {
        return User::factory()->create(['is_admin' => false, 'is_applicant' => true, 'is_active' => true, 'email_verified_at' => now()]);
    }

    private function app(User $user): PPDBRegistration
    {
        Storage::fake('ppdb_private');
        $program = Program::factory()->create(['status' => 'active']);
        $period = PpdbPeriod::first();
        $app = PPDBRegistration::create([
            'name' => 'Siswa Dok', 'gender' => 'laki-laki', 'program_id' => $program->id,
            'applicant_account_id' => $user->id, 'period_id' => $period->id, 'source' => 'applicant',
        ]);
        \App\Services\DocumentService::ensurePlaceholders($app);

        return $app->fresh();
    }

    private function files(): array
    {
        return [
            'kk' => 'kk-test.pdf',
            'ktp_ortu' => 'ktp-test.pdf',
            'akta' => 'akta-test.pdf',
            'rapor' => 'rapor-test.pdf',
            'foto' => 'foto-test.jpg',
        ];
    }

    private function fakeFor(string $type, string $filename): UploadedFile
    {
        $mime = str_ends_with($filename, '.jpg') ? 'image/jpeg' : 'application/pdf';

        return UploadedFile::fake()->create($filename, 200, $mime);
    }

    private function uploadAll(User $user, PPDBRegistration $app, array $order): void
    {
        foreach ($order as $type) {
            $this->actingAs($user)->post(
                route('portal.documents.upload', [$app, $type]),
                ['file' => $this->fakeFor($type, $this->files()[$type])]
            )->assertRedirect();
        }
    }

    private function assertMapping(PPDBRegistration $app): void
    {
        foreach ($this->files() as $type => $filename) {
            $doc = $app->documents()->where('type', $type)->first();
            $this->assertNotNull($doc, "Dokumen {$type} harus ada.");
            $this->assertEquals($filename, $doc->original_name, "Slot {$type} salah petakan file.");
        }
        // Tepat satu record berjalan per application+type.
        $this->assertEquals(5, $app->documents()->whereNotNull('path')->count());
    }

    public function test_forward_order_mapping_is_exact(): void
    {
        $user = $this->applicant();
        $app = $this->app($user);
        $this->uploadAll($user, $app, ['kk', 'ktp_ortu', 'akta', 'rapor', 'foto']);
        $this->assertMapping($app->fresh());
    }

    public function test_reverse_order_mapping_is_exact(): void
    {
        $user = $this->applicant();
        $app = $this->app($user);
        $this->uploadAll($user, $app, ['foto', 'rapor', 'akta', 'ktp_ortu', 'kk']);
        $this->assertMapping($app->fresh());
    }

    public function test_replacement_only_affects_target_document(): void
    {
        $user = $this->applicant();
        $app = $this->app($user);
        $this->uploadAll($user, $app, ['kk', 'ktp_ortu', 'akta', 'rapor', 'foto']);

        $this->actingAs($user)->post(
            route('portal.documents.upload', [$app, 'rapor']),
            ['file' => UploadedFile::fake()->create('rapor-baru.pdf', 200, 'application/pdf')]
        )->assertRedirect();

        $app = $app->fresh();
        $this->assertEquals('rapor-baru.pdf', $app->documents()->where('type', 'rapor')->first()->original_name);
        $this->assertEquals(2, $app->documents()->where('type', 'rapor')->first()->version);
        $this->assertEquals(1, $app->documents()->where('type', 'rapor')->first()->revisions()->count());
        foreach (['kk' => 'kk-test.pdf', 'ktp_ortu' => 'ktp-test.pdf', 'akta' => 'akta-test.pdf', 'foto' => 'foto-test.jpg'] as $type => $filename) {
            $this->assertEquals($filename, $app->documents()->where('type', $type)->first()->original_name, "Slot {$type} ikut berubah!");
            $this->assertEquals(1, $app->documents()->where('type', $type)->first()->version);
        }
    }

    public function test_multi_student_isolation(): void
    {
        $user = $this->applicant();
        $program = Program::factory()->create(['status' => 'active']);
        $period = PpdbPeriod::first();
        Storage::fake('ppdb_private');
        $mkApp = fn ($name) => PPDBRegistration::create([
            'name' => $name, 'gender' => 'laki-laki', 'program_id' => $program->id,
            'applicant_account_id' => $user->id, 'period_id' => $period->id, 'source' => 'applicant',
        ]);
        $a = $mkApp('Anak A');
        $b = $mkApp('Anak B');
        \App\Services\DocumentService::ensurePlaceholders($a);
        \App\Services\DocumentService::ensurePlaceholders($b);

        $this->actingAs($user)->post(route('portal.documents.upload', [$a, 'kk']), ['file' => $this->fakeFor('kk', 'kk-milik-a.pdf')])->assertRedirect();
        $this->actingAs($user)->post(route('portal.documents.upload', [$b, 'kk']), ['file' => $this->fakeFor('kk', 'kk-milik-b.pdf')])->assertRedirect();

        $this->assertEquals('kk-milik-a.pdf', $a->fresh()->documents()->where('type', 'kk')->first()->original_name);
        $this->assertEquals('kk-milik-b.pdf', $b->fresh()->documents()->where('type', 'kk')->first()->original_name);
    }

    public function test_idor_blocked_for_other_account_document(): void
    {
        $a = $this->applicant();
        $b = $this->applicant();
        $appB = $this->app($b);

        $this->actingAs($a)->post(
            route('portal.documents.upload', [$appB, 'kk']),
            ['file' => $this->fakeFor('kk', 'jahat.pdf')]
        )->assertForbidden();
        $this->assertNull($appB->fresh()->documents()->where('type', 'kk')->first()->path);

        $docB = $appB->documents()->where('type', 'kk')->first();
        $this->actingAs($a)->get(route('portal.documents.preview', $docB))->assertForbidden();
        $this->actingAs($a)->get(route('admin.documents.preview', $docB))->assertForbidden();
    }

    public function test_invalid_document_type_rejected_without_mutation(): void
    {
        $user = $this->applicant();
        $app = $this->app($user);
        $before = $app->documents()->whereNotNull('path')->count();

        $this->actingAs($user)->post(
            route('portal.documents.upload', [$app, 'random_value']),
            ['file' => $this->fakeFor('kk', 'x.pdf')]
        )->assertNotFound();
        $this->actingAs($user)->post(
            route('portal.documents.upload', [$app, '../../etc']),
            ['file' => $this->fakeFor('kk', 'x.pdf')]
        )->assertNotFound();
        $this->assertEquals($before, $app->fresh()->documents()->whereNotNull('path')->count());
    }

    public function test_mime_policy_per_type(): void
    {
        $user = $this->applicant();
        $app = $this->app($user);

        // Foto menolak PDF.
        $this->actingAs($user)->post(
            route('portal.documents.upload', [$app, 'foto']),
            ['file' => UploadedFile::fake()->create('foto.pdf', 200, 'application/pdf')]
        )->assertSessionHasErrors(['file']);
        // Executable ditolak di semua slot.
        $this->actingAs($user)->post(
            route('portal.documents.upload', [$app, 'kk']),
            ['file' => UploadedFile::fake()->create('jahat.php', 100, 'application/x-php')]
        )->assertSessionHasErrors(['file']);
        // Pesan manusiawi, bukan kunci validasi.
        foreach ((array) session('errors')?->all() as $msg) {
            $this->assertDoesNotMatchRegularExpression('/^(validation|auth|passwords)\./', $msg);
        }
    }

    public function test_dom_ids_are_unique_per_card(): void
    {
        $user = $this->applicant();
        $app = $this->app($user);
        $html = $this->actingAs($user)->get(route('portal.applications.show', [$app, 'tahap' => 'dokumen']))->assertOk()->getContent();

        foreach (['kk', 'ktp_ortu', 'akta', 'rapor', 'foto'] as $type) {
            $this->assertStringContainsString('id="doc-upload-'.$type.'-file"', $html, "Input unik {$type} hilang.");
        }
        // Tidak ada lagi id generik ganda.
        $this->assertEquals(0, substr_count($html, 'id="file-file"'));
        preg_match_all('/id="([^"]+)"/', $html, $m);
        $counts = array_count_values($m[1]);
        unset($counts['main-content']); // duplikat layout pra-ada, di luar cakupan upload.
        $dupes = array_filter($counts, fn ($c) => $c > 1);
        $this->assertSame([], $dupes, 'ID ganda di halaman dokumen: '.implode(', ', array_keys($dupes)));
    }
}
