<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AnnouncementRegressionTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    public function test_admin_can_create_published_announcement_visible_on_public_index_immediately(): void
    {
        $admin = $this->admin();
        $title = 'REAL-PUBLISH-TEST-'.time().'-1';

        $this->actingAs($admin)->post(route('admin.announcements.store'), [
            'title' => $title,
            'content' => 'Isi',
            'status' => 'published',
            'published_at' => now('Asia/Jakarta')->format('Y-m-d\TH:i'),
        ])->assertRedirect(route('admin.announcements.index'));

        $this->get(route('announcements.index'))->assertOk()->assertSee($title);
    }

    public function test_admin_can_create_published_announcement_visible_on_home_immediately(): void
    {
        $admin = $this->admin();
        $title = 'REAL-PUBLISH-HOME-'.time();

        $this->actingAs($admin)->post(route('admin.announcements.store'), [
            'title' => $title,
            'content' => 'Isi home',
            'status' => 'published',
            // leave published_at empty -> should default to now and be visible
        ])->assertRedirect();

        $this->get(route('home'))->assertOk()->assertSee($title);
    }

    public function test_published_announcement_cache_is_invalidated_after_create(): void
    {
        Cache::put('home:announcements', collect([]), 3600);
        $this->assertTrue(Cache::has('home:announcements'));

        $admin = $this->admin();
        $title = 'CACHE-TEST-'.time();
        $this->actingAs($admin)->post(route('admin.announcements.store'), [
            'title' => $title,
            'content' => 'x',
            'status' => 'published',
        ])->assertRedirect();

        $this->assertFalse(Cache::has('home:announcements'), 'Cache should be cleared after create');
        $this->get(route('home'))->assertOk()->assertSee($title);
    }

    public function test_published_announcement_cache_is_invalidated_after_update(): void
    {
        $ann = Announcement::factory()->create(['status' => 'draft', 'title' => 'Draft Title']);
        Cache::put('home:announcements', collect([]), 3600);

        $admin = $this->admin();
        $this->actingAs($admin)->put(route('admin.announcements.update', $ann), [
            'title' => 'Updated Published',
            'content' => 'x',
            'status' => 'published',
            'published_at' => now('Asia/Jakarta')->format('Y-m-d\TH:i'),
        ])->assertRedirect();

        $this->assertFalse(Cache::has('home:announcements'));
        $this->get(route('announcements.index'))->assertSee('Updated Published');
    }

    public function test_deleted_announcement_disappears_from_public(): void
    {
        $ann = Announcement::factory()->create(['status' => 'published', 'published_at' => now()->subDay(), 'title' => 'ToDelete']);
        $this->get(route('announcements.index'))->assertSee('ToDelete');

        $admin = $this->admin();
        $this->actingAs($admin)->delete(route('admin.announcements.destroy', $ann))->assertRedirect();
        $this->get(route('announcements.index'))->assertDontSee('ToDelete');
        $this->get(route('home'))->assertDontSee('ToDelete');
    }

    public function test_restored_published_announcement_reappears(): void
    {
        $ann = Announcement::factory()->create(['status' => 'published', 'published_at' => now()->subDay(), 'title' => 'RestoreMe']);
        $admin = $this->admin();
        $this->actingAs($admin)->delete(route('admin.announcements.destroy', $ann));
        $this->get(route('announcements.index'))->assertDontSee('RestoreMe');

        $this->actingAs($admin)->post(route('admin.announcements.restore', $ann->id))->assertRedirect();
        $this->get(route('announcements.index'))->assertSee('RestoreMe');
    }

    public function test_draft_announcement_does_not_appear(): void
    {
        Announcement::factory()->create(['status' => 'draft', 'title' => 'DraftHidden', 'published_at' => now()]);
        $this->get(route('announcements.index'))->assertDontSee('DraftHidden');
        $this->get(route('home'))->assertDontSee('DraftHidden');
    }

    public function test_future_scheduled_announcement_does_not_appear(): void
    {
        Announcement::factory()->create([
            'status' => 'published',
            'published_at' => now()->addDays(2),
            'title' => 'FutureHidden',
        ]);
        $this->get(route('announcements.index'))->assertDontSee('FutureHidden');
        $this->get(route('home'))->assertDontSee('FutureHidden');
    }

    public function test_publish_now_uses_correct_application_timezone(): void
    {
        $admin = $this->admin();
        $jakartaNow = now('Asia/Jakarta')->format('Y-m-d\TH:i');
        $title = 'TZ-TEST-'.time();
        $this->actingAs($admin)->post(route('admin.announcements.store'), [
            'title' => $title,
            'content' => 'x',
            'status' => 'published',
            'published_at' => $jakartaNow,
        ])->assertRedirect();

        $ann = Announcement::where('title', $title)->first();
        $this->assertNotNull($ann);
        // stored should be UTC equivalent of Jakarta input, and be <= now (UTC)
        $this->assertTrue($ann->published_at->lte(now()), 'published_at should be <= now (UTC) when Jakarta local now is used');
        $this->assertTrue(Announcement::published()->where('id', $ann->id)->exists());
    }
}
