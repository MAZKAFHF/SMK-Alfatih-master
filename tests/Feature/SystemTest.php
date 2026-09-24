<?php

namespace Tests\Feature;

use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
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
        $this->artisan('app:backup', ['--retention' => 2])->assertExitCode(0);
        $files = glob(storage_path('app/backups/db-*'));
        $this->assertNotEmpty($files);
        $latest = max($files);
        $this->assertGreaterThan(0, filesize($latest));
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
