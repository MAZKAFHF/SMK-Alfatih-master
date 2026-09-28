<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\Page;
use App\Models\PPDBRegistration;
use App\Models\PpdbPeriod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class SystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_navbar_cache_invalidation(): void
    {
        Cache::forget('nav_pages');
        Page::factory()->create(['title' => 'Cache Test', 'slug' => 'cache-test', 'status' => 'published', 'order' => 1]);
        $first = Cache::remember('nav_pages', 3600, fn() => Page::published()->orderBy('order')->limit(6)->get(['title', 'slug']));
        $this->assertTrue($first->contains('slug', 'cache-test'));
        Page::factory()->create(['title' => 'Second', 'slug' => 'second-page', 'status' => 'published', 'order' => 2]);
        Cache::forget('nav_pages');
        $second = Cache::remember('nav_pages', 3600, fn() => Page::published()->orderBy('order')->limit(6)->get(['title', 'slug']));
        $this->assertTrue($second->contains('slug', 'second-page'));
    }

    public function test_health_endpoint_returns_ok(): void
    {
        $this->get('/health')->assertOk()->assertJson(['status' => 'ok']);
        $this->get('/health')->assertJsonPath('checks.database', 'ok');
        $this->assertStringNotContainsString('password', strtolower($this->get('/health')->getContent()));
    }

    public function test_health_does_not_expose_secrets(): void
    {
        $content = $this->get('/health')->getContent();
        $this->assertStringNotContainsString(config('database.connections.sqlite.database') ?? '', $content);
        $this->assertStringNotContainsString('APP_KEY', $content);
    }

    public function test_backup_command_succeeds_sqlite(): void
    {
        $backupDir = storage_path('framework/testing/backups');
        File::deleteDirectory($backupDir);
        $this->artisan('app:backup', ['--retention' => 2])->assertExitCode(0);
        $files = glob($backupDir.'/db-*');
        $this->assertNotEmpty($files);
        $latest = max($files);
        $this->assertGreaterThan(0, filesize($latest));
        $this->assertNotEmpty(glob($backupDir.'/files-*.tar.gz'));
        $this->assertNotEmpty(glob($backupDir.'/manifest-*.json'));
        $this->artisan('app:backup-verify')->assertExitCode(0);
        File::deleteDirectory($backupDir);
    }

    public function test_e2e_cleanup_previews_then_removes_only_marked_fixtures(): void
    {
        $normalPeriod = PpdbPeriod::query()->firstOrFail();
        $e2ePeriod = PpdbPeriod::create([
            'academic_year' => 'E2E-CLEANUP-TEST',
            'status' => PpdbPeriod::STATUS_CLOSED,
            'is_open' => false,
        ]);
        $normalUser = User::factory()->create(['email' => 'operator@alfatih.sch.id']);
        $e2eUser = User::factory()->create(['email' => 'cleanup@window.test']);

        $periodFixture = PPDBRegistration::factory()->create([
            'period_id' => $e2ePeriod->id,
            'applicant_account_id' => $normalUser->id,
        ]);
        $userFixture = PPDBRegistration::factory()->create([
            'period_id' => $normalPeriod->id,
            'applicant_account_id' => $e2eUser->id,
        ]);
        $normalApplication = PPDBRegistration::factory()->create([
            'period_id' => $normalPeriod->id,
            'applicant_account_id' => $normalUser->id,
        ]);
        $e2eAnnouncement = Announcement::factory()->create(['title' => 'E2E-ANN-cleanup-test']);
        $normalAnnouncement = Announcement::factory()->create(['title' => 'Pengumuman operasional']);

        $this->artisan('app:cleanup-e2e')->assertSuccessful();
        $this->assertDatabaseHas('users', ['id' => $e2eUser->id]);
        $this->assertDatabaseHas('ppdb_registrations', ['id' => $periodFixture->id]);

        $this->artisan('app:cleanup-e2e', ['--force' => true])->assertSuccessful();

        $this->assertDatabaseMissing('ppdb_periods', ['id' => $e2ePeriod->id]);
        $this->assertDatabaseMissing('users', ['id' => $e2eUser->id]);
        $this->assertDatabaseMissing('ppdb_registrations', ['id' => $periodFixture->id]);
        $this->assertDatabaseMissing('ppdb_registrations', ['id' => $userFixture->id]);
        $this->assertDatabaseMissing('announcements', ['id' => $e2eAnnouncement->id]);
        $this->assertDatabaseHas('ppdb_periods', ['id' => $normalPeriod->id]);
        $this->assertDatabaseHas('users', ['id' => $normalUser->id]);
        $this->assertDatabaseHas('ppdb_registrations', ['id' => $normalApplication->id]);
        $this->assertDatabaseHas('announcements', ['id' => $normalAnnouncement->id]);
    }

    public function test_error_pages_have_correct_status(): void
    {
        $this->get('/nonexistent-page-xyz123')->assertNotFound();
        $this->get('/admin')->assertRedirect(route('admin.login'));
        $admin = \App\Models\User::factory()->create(['is_admin' => false]);
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_429_is_handled(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('contact.send'), [
                'name' => "User $i",
                'email' => "u$i@example.com",
                'subject' => "Sub $i",
                'message' => "Msg $i",
            ]);
        }
        $this->post(route('contact.send'), [
            'name' => 'Overflow',
            'email' => 'overflow@example.com',
            'subject' => 'Overflow',
            'message' => 'Overflow',
        ])->assertStatus(429);
    }
}
