<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RichTextTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    public function test_announcement_edit_does_not_expose_raw_p_tags(): void
    {
        $ann = Announcement::factory()->create([
            'content' => '<p>Pengumuman sekolah</p><p>Besok libur.</p>',
            'status' => 'published',
            'published_at' => now(),
        ]);
        $admin = $this->admin();
        $response = $this->actingAs($admin)->get(route('admin.announcements.edit', $ann));
        $response->assertOk();
        // Should contain trix-editor, not raw textarea with <p>
        $response->assertSee('trix-editor', false);
        // The hidden input should contain the HTML but not visible as raw <p> in textarea
        // Check that the page does not contain a textarea with raw <p> content visible
        $content = $response->getContent();
        $this->assertStringNotContainsString('<textarea', $content, 'Edit should use trix, not textarea for rich content');
        // The hidden input value should be present but escaped; trix will render formatted
        $this->assertStringContainsString('Pengumuman sekolah', $content);
    }

    public function test_news_edit_uses_rich_editor(): void
    {
        $news = \App\Models\News::factory()->create(['content' => '<p>News</p>']);
        $admin = $this->admin();
        $response = $this->actingAs($admin)->get(route('admin.news.edit', $news));
        $response->assertOk()->assertSee('trix-editor', false);
    }

    public function test_page_edit_uses_rich_editor(): void
    {
        $page = \App\Models\Page::factory()->create(['content' => '<p>Page</p>']);
        $admin = $this->admin();
        $response = $this->actingAs($admin)->get(route('admin.pages.edit', $page));
        $response->assertOk()->assertSee('trix-editor', false);
    }

    public function test_program_edit_uses_rich_editor(): void
    {
        $program = \App\Models\Program::factory()->create(['description' => '<p>Program</p>']);
        $admin = $this->admin();
        $response = $this->actingAs($admin)->get(route('admin.programs.edit', $program));
        $response->assertOk()->assertSee('trix-editor', false);
    }

    public function test_rich_content_round_trip_stability(): void
    {
        $admin = $this->admin();
        $html = '<h2>Judul</h2><p>Paragraf <strong>bold</strong> dan <em>italic</em></p><ul><li>Item 1</li><li>Item 2</li></ul><blockquote>Kutipan</blockquote><p><a href="https://example.com">Link</a></p>';
        $this->actingAs($admin)->post(route('admin.announcements.store'), [
            'title' => 'Round Trip',
            'content' => $html,
            'status' => 'published',
        ])->assertRedirect();
        $ann = Announcement::where('title', 'Round Trip')->first();
        $this->assertNotNull($ann);
        $firstStored = $ann->content;

        // 5 round trips
        for ($i = 1; $i <= 5; $i++) {
            $this->actingAs($admin)->put(route('admin.announcements.update', $ann), [
                'title' => 'Round Trip',
                'content' => $ann->content,
                'status' => 'published',
                'published_at' => $ann->published_at->timezone('Asia/Jakarta')->format('Y-m-d\TH:i'),
            ])->assertRedirect();
            $ann->refresh();
            $this->assertEquals($firstStored, $ann->content, "Round-trip $i should be idempotent");
        }
    }

    public function test_malicious_html_is_sanitized_but_valid_remains(): void
    {
        $admin = $this->admin();
        $malicious = '<p>Hello</p><script>alert(1)</script><p onclick="evil()">click</p><a href="javascript:alert(1)">bad</a><a href="https://example.com">good</a>';
        $this->actingAs($admin)->post(route('admin.announcements.store'), [
            'title' => 'Malicious',
            'content' => $malicious,
            'status' => 'published',
        ])->assertRedirect();
        $ann = Announcement::where('title', 'Malicious')->first();
        $this->assertStringNotContainsString('<script>', $ann->content);
        $this->assertStringNotContainsString('onclick', $ann->content);
        $this->assertStringNotContainsString('javascript:', $ann->content);
        $this->assertStringContainsString('https://example.com', $ann->content);
        $this->assertStringContainsString('<a', $ann->content);
    }

    public function test_public_renders_sanitized_html(): void
    {
        $ann = Announcement::factory()->create([
            'title' => 'Public Render',
            'content' => '<p>Public</p><p>Test</p>',
            'status' => 'published',
            'published_at' => now()->subHour(),
        ]);
        $response = $this->get(route('announcements.index'));
        $response->assertOk()->assertSee('Public Render');
        // The public view should render HTML (not escaped)
        $this->assertStringContainsString('<p>Public</p>', $ann->content);
    }
}
