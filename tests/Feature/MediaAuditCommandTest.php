<?php

namespace Tests\Feature;

use App\Models\Gallery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaAuditCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_media_audit_passes_when_references_exist_and_never_deletes_orphans(): void
    {
        Storage::fake('public');
        Storage::fake('ppdb_private');
        Storage::disk('public')->put('galleries/used.webp', 'used');
        Storage::disk('public')->put('galleries/orphan.webp', 'orphan');
        Gallery::factory()->create(['image' => 'galleries/used.webp']);

        $this->artisan('app:media-audit', ['--json' => true])->assertSuccessful();

        Storage::disk('public')->assertExists('galleries/orphan.webp');
    }

    public function test_media_audit_fails_when_database_file_is_missing(): void
    {
        Storage::fake('public');
        Storage::fake('ppdb_private');
        Gallery::factory()->create(['image' => 'galleries/missing.webp']);

        $this->artisan('app:media-audit', ['--json' => true])->assertFailed();
    }
}
