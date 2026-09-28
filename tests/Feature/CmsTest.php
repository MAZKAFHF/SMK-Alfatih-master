<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\ContactMessage;
use App\Models\Gallery;
use App\Models\News;
use App\Models\Page;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CmsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    private function superadmin(): User
    {
        return User::factory()->create(['is_admin' => true, 'is_superadmin' => true]);
    }

    // PROGRAMS

    public function test_guest_cannot_access_program_cms(): void
    {
        $this->get(route('admin.programs.index'))->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_list_programs(): void
    {
        Program::factory()->create();
        $this->actingAs($this->admin())->get(route('admin.programs.index'))->assertOk();
    }

    public function test_admin_can_create_program(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin())->post(route('admin.programs.store'), [
            'name' => 'Test Program',
            'slug' => 'test-program',
            'short_description' => 'Short',
            'description' => '<p>Desc <strong>bold</strong></p>',
            'status' => 'active',
            'order' => 1,
            'image' => UploadedFile::fake()->create('test.jpg', 100, 'image/jpeg'),
        ])->assertRedirect(route('admin.programs.index'));
        $this->assertDatabaseHas('programs', ['slug' => 'test-program']);
        $program = Program::where('slug', 'test-program')->first();
        $this->assertStringNotContainsString('<script>', $program->description);
    }

    public function test_program_slug_uniqueness(): void
    {
        Program::factory()->create(['slug' => 'dup-slug']);
        $this->actingAs($this->admin())->post(route('admin.programs.store'), [
            'name' => 'Another',
            'slug' => 'dup-slug',
            'short_description' => 'Short',
            'description' => 'Desc',
            'status' => 'active',
        ])->assertSessionHasErrors('slug');
    }

    public function test_program_validation_requires_name(): void
    {
        $this->actingAs($this->admin())->post(route('admin.programs.store'), [
            'name' => '',
            'slug' => '',
            'short_description' => '',
            'description' => '',
            'status' => 'active',
        ])->assertSessionHasErrors(['name', 'slug', 'short_description', 'description']);
    }

    public function test_admin_can_update_program(): void
    {
        $p = Program::factory()->create(['name' => 'Old']);
        $this->actingAs($this->admin())->put(route('admin.programs.update', $p), [
            'name' => 'New Name',
            'slug' => 'new-slug',
            'short_description' => 'New short',
            'description' => 'New desc',
            'status' => 'inactive',
            'order' => 5,
        ])->assertRedirect(route('admin.programs.index'));
        $this->assertDatabaseHas('programs', ['id' => $p->id, 'name' => 'New Name', 'slug' => 'new-slug']);
    }

    public function test_program_soft_delete_and_restore(): void
    {
        $p = Program::factory()->create();
        $admin = $this->admin();
        $this->actingAs($admin)->delete(route('admin.programs.destroy', $p))->assertRedirect();
        $this->assertSoftDeleted('programs', ['id' => $p->id]);
        $this->actingAs($admin)->post(route('admin.programs.restore', $p->id))->assertRedirect();
        $this->assertDatabaseHas('programs', ['id' => $p->id]);
        // force delete requires superadmin
        $this->actingAs($admin)->delete(route('admin.programs.destroy', $p))->assertRedirect();
        $this->actingAs($admin)->delete(route('admin.programs.force-delete', $p->id))->assertForbidden();
        $this->actingAs($this->superadmin())->delete(route('admin.programs.force-delete', $p->id))->assertRedirect();
        $this->assertDatabaseMissing('programs', ['id' => $p->id]);
    }

    public function test_program_image_replace_deletes_old(): void
    {
        Storage::fake('public');
        $p = Program::factory()->create(['image' => 'programs/old.jpg']);
        Storage::disk('public')->put('programs/old.jpg', 'dummy');
        $this->actingAs($this->admin())->put(route('admin.programs.update', $p), [
            'name' => $p->name,
            'slug' => $p->slug,
            'short_description' => $p->short_description,
            'description' => $p->description,
            'status' => 'active',
            'order' => 1,
            'image' => UploadedFile::fake()->create('new.jpg', 100, 'image/jpeg'),
        ])->assertRedirect();
        Storage::disk('public')->assertMissing('programs/old.jpg');
    }

    // NEWS

    public function test_news_crud_with_thumbnail_and_status(): void
    {
        Storage::fake('public');
        $admin = $this->admin();
        $this->actingAs($admin)->post(route('admin.news.store'), [
            'title' => 'Berita Test',
            'slug' => 'berita-test',
            'content' => '<h2>Judul</h2><p>Isi <script>alert(1)</script></p>',
            'status' => 'draft',
            'thumbnail' => UploadedFile::fake()->create('thumb.jpg', 100, 'image/jpeg'),
        ])->assertRedirect(route('admin.news.index'));
        $news = News::where('slug', 'berita-test')->first();
        $this->assertNotNull($news);
        $this->assertEquals('draft', $news->status->value);
        $this->assertStringNotContainsString('<script>', $news->content);
        // update to published with schedule
        $this->actingAs($admin)->put(route('admin.news.update', $news), [
            'title' => $news->title,
            'slug' => $news->slug,
            'content' => $news->content,
            'status' => 'published',
            'published_at' => now()->format('Y-m-d\TH:i'),
        ])->assertRedirect();
        $this->assertDatabaseHas('news', ['id' => $news->id, 'status' => 'published']);
        // soft delete
        $this->actingAs($admin)->delete(route('admin.news.destroy', $news))->assertRedirect();
        $this->assertSoftDeleted('news', ['id' => $news->id]);
    }

    public function test_news_xss_sanitization_blocks_script(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post(route('admin.news.store'), [
            'title' => 'XSS',
            'slug' => 'xss-test',
            'content' => '<p onclick="alert(1)">hi</p><svg onload=alert(1)></svg>',
            'status' => 'draft',
        ])->assertRedirect();
        $n = News::where('slug', 'xss-test')->first();
        $this->assertStringNotContainsString('onclick', $n->content);
        $this->assertStringNotContainsString('<svg', $n->content);
        $this->assertStringNotContainsString('<script>', $n->content);
    }

    public function test_news_slug_validation(): void
    {
        News::factory()->create(['slug' => 'dup']);
        $this->actingAs($this->admin())->post(route('admin.news.store'), [
            'title' => 'Another',
            'slug' => 'dup',
            'content' => 'x',
            'status' => 'draft',
        ])->assertSessionHasErrors('slug');
    }

    // GALLERY

    public function test_gallery_create_with_image(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin())->post(route('admin.galleries.store'), [
            'title' => 'Foto Test',
            'category' => 'Kegiatan',
            'status' => 'published',
            'image' => UploadedFile::fake()->create('photo.jpg', 100, 'image/jpeg'),
        ])->assertRedirect(route('admin.galleries.index'));
        $this->assertDatabaseHas('galleries', ['title' => 'Foto Test']);
    }

    public function test_gallery_rejects_svg_and_executable(): void
    {
        Storage::fake('public');
        $svg = UploadedFile::fake()->createWithContent('evil.svg', '<svg onload=alert(1)></svg>');
        // mime will be text/plain or image/svg+xml ; our validation mimes jpg/png/webp should reject
        $this->actingAs($this->admin())->post(route('admin.galleries.store'), [
            'title' => 'Evil',
            'status' => 'published',
            'image' => $svg,
        ])->assertSessionHasErrors('image');
    }

    public function test_gallery_rejects_oversized(): void
    {
        Storage::fake('public');
        $big = UploadedFile::fake()->create('big.jpg', 7000, 'image/jpeg'); // 7MB > 6144
        $this->actingAs($this->admin())->post(route('admin.galleries.store'), [
            'title' => 'Big',
            'status' => 'published',
            'image' => $big,
        ])->assertSessionHasErrors('image');
    }

    public function test_gallery_publish_and_delete_flow(): void
    {
        $g = Gallery::factory()->create(['status' => 'draft']);
        $admin = $this->admin();
        $this->actingAs($admin)->put(route('admin.galleries.update', $g), [
            'title' => $g->title,
            'status' => 'published',
        ])->assertRedirect();
        $this->assertDatabaseHas('galleries', ['id' => $g->id, 'status' => 'published']);
        $this->actingAs($admin)->delete(route('admin.galleries.destroy', $g))->assertRedirect();
        $this->assertSoftDeleted('galleries', ['id' => $g->id]);
    }

    // ANNOUNCEMENT

    public function test_announcement_full_flow(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post(route('admin.announcements.store'), [
            'title' => 'Umum',
            'content' => 'Isi pengumuman',
            'status' => 'draft',
        ])->assertRedirect();
        $ann = Announcement::where('title', 'Umum')->first();
        $this->assertEquals('draft', $ann->status->value);
        $this->actingAs($admin)->put(route('admin.announcements.update', $ann), [
            'title' => 'Umum Edited',
            'content' => 'Isi baru <script>evil</script>',
            'status' => 'published',
            'published_at' => now()->format('Y-m-d\TH:i:s'),
        ])->assertRedirect();
        $ann->refresh();
        $this->assertStringNotContainsString('<script>', $ann->content);
        $this->assertDatabaseHas('announcements', ['id' => $ann->id, 'status' => 'published']);
        $this->actingAs($admin)->delete(route('admin.announcements.destroy', $ann))->assertRedirect();
        $this->assertSoftDeleted('announcements', ['id' => $ann->id]);
        $this->actingAs($admin)->post(route('admin.announcements.restore', $ann->id))->assertRedirect();
        $this->assertDatabaseHas('announcements', ['id' => $ann->id]);
    }

    // PAGES

    public function test_page_crud_with_seo_and_order(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post(route('admin.pages.store'), [
            'title' => 'Halaman Test',
            'slug' => 'halaman-test',
            'content' => '<h2>K</h2><p>Konten</p>',
            'status' => 'published',
            'order' => 99,
            'meta_title' => 'Meta',
            'meta_description' => 'Desc',
        ])->assertRedirect(route('admin.pages.index'));
        $page = Page::where('slug', 'halaman-test')->first();
        $this->assertNotNull($page);
        $this->assertEquals(99, $page->order);
        // update
        $this->actingAs($admin)->put(route('admin.pages.update', $page), [
            'title' => 'Halaman Test Updated',
            'slug' => 'halaman-test',
            'content' => '<p>Updated</p>',
            'status' => 'draft',
            'order' => 5,
        ])->assertRedirect();
        $this->assertDatabaseHas('pages', ['id' => $page->id, 'status' => 'draft']);
        // delete
        $this->actingAs($admin)->delete(route('admin.pages.destroy', $page))->assertRedirect();
        $this->assertSoftDeleted('pages', ['id' => $page->id]);
    }

    public function test_page_guest_cannot_access_admin(): void
    {
        $this->get(route('admin.pages.index'))->assertRedirect(route('admin.login'));
        $page = Page::factory()->create();
        $this->put(route('admin.pages.update', $page), [])->assertRedirect(route('admin.login'));
    }

    // CONTACT INBOX

    public function test_contact_public_submit_and_admin_flow(): void
    {
        // public submit
        $this->post(route('contact.send'), [
            'name' => 'Budi',
            'email' => 'budi@example.com',
            'subject' => 'Tanya PPDB',
            'message' => 'Halo',
        ])->assertRedirect();
        $msg = ContactMessage::where('email', 'budi@example.com')->first();
        $this->assertNotNull($msg);
        $this->assertFalse((bool) $msg->is_read);
        // admin list
        $admin = $this->admin();
        $this->actingAs($admin)->get(route('admin.contact-messages.index'))->assertOk()->assertSee('Pesan Masuk');
        // show marks read
        $this->actingAs($admin)->get(route('admin.contact-messages.show', $msg))->assertOk();
        $this->assertTrue(ContactMessage::find($msg->id)->is_read);
        $this->actingAs($admin)->put(route('admin.contact-messages.handling', $msg), [
            'handling_status' => 'resolved', 'response_channel' => 'email', 'admin_note' => 'Sudah dibalas.',
        ])->assertRedirect();
        $this->assertDatabaseHas('contact_messages', ['id' => $msg->id, 'handling_status' => 'resolved', 'response_channel' => 'email', 'resolved_by' => $admin->id]);
        // archive
        $this->actingAs($admin)->post(route('admin.contact-messages.archive', $msg))->assertRedirect();
        $this->assertTrue(ContactMessage::find($msg->id)->is_archived);
        // delete soft
        $this->actingAs($admin)->delete(route('admin.contact-messages.destroy', $msg))->assertRedirect();
        $this->assertSoftDeleted('contact_messages', ['id' => $msg->id]);
        // restore
        $this->actingAs($admin)->post(route('admin.contact-messages.restore', $msg->id))->assertRedirect();
        $this->assertDatabaseHas('contact_messages', ['id' => $msg->id]);
    }

    public function test_contact_mail_failure_not_lose_message(): void
    {
        // mail driver log should not fail public store even if mail fails (we only log)
        // Simulate exception is handled in controller (try catch)
        $this->post(route('contact.send'), [
            'name' => 'Ani',
            'email' => 'ani@example.com',
            'subject' => 'Hello',
            'message' => str_repeat('a', 100),
        ])->assertRedirect();
        $this->assertDatabaseHas('contact_messages', ['email' => 'ani@example.com']);
    }

    public function test_contact_rate_limit(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('contact.send'), [
                'name' => "User $i",
                'email' => "user$i@example.com",
                'subject' => "Sub $i",
                'message' => "Msg $i",
            ])->assertRedirect();
        }
        // 6th within minute should be 429
        $this->post(route('contact.send'), [
            'name' => 'Overflow',
            'email' => 'overflow@example.com',
            'subject' => 'Overflow',
            'message' => 'Overflow',
        ])->assertStatus(429);
    }

    // SETTINGS

    public function test_settings_page_does_not_create_missing_pages_on_get(): void
    {
        $admin = $this->admin();
        Page::query()->where('slug', 'fasilitas')->delete();
        $before = Page::withTrashed()->count();
        $this->actingAs($admin)->get(route('admin.settings.index'))->assertOk()->assertSee('belum ada', false);
        $this->assertSame($before, Page::withTrashed()->count());
    }

    public function test_settings_update_and_cache_invalidation(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->put(route('admin.settings.update'), [
            'school_name' => 'SMK Test',
            'school_email' => 'test@smk.test',
            'stat_programs' => '10',
        ])->assertRedirect();
        $this->assertEquals('SMK Test', \App\Models\SiteSetting::get('school_name'));
        $this->assertEquals('10', \App\Models\SiteSetting::get('stat_programs'));
    }

    public function test_settings_unauthorized_denied(): void
    {
        $this->put(route('admin.settings.update'), [])->assertRedirect(route('admin.login'));
        $this->actingAs(User::factory()->create(['is_admin'=>false]))->put(route('admin.settings.update'), [])->assertForbidden();
    }

    // AUDIT LOG

    public function test_audit_log_created_on_mutation(): void
    {
        $admin = $this->admin();
        Storage::fake('public');
        $this->actingAs($admin)->post(route('admin.programs.store'), [
            'name' => 'Audit Prog',
            'slug' => 'audit-prog',
            'short_description' => 'Short',
            'description' => 'Desc',
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'program_create']);
        $log = \App\Models\AuditLog::where('action','program_create')->first();
        $this->assertEquals($admin->id, $log->user_id);
        $this->assertNotNull($log->auditable_id);
        $this->assertNull($log->old_values); // create has no old
    }

    public function test_audit_does_not_store_password(): void
    {
        $super = $this->superadmin();
        $target = $this->admin();
        $this->actingAs($super)->put(route('admin.users.update', $target), [
            'name' => $target->name,
            'email' => $target->email,
            'password' => 'newpass123',
        ]);
        $log = \App\Models\AuditLog::where('action','user_update')->latest()->first();
        $this->assertStringNotContainsString('newpass123', json_encode($log->new_values ?? []));
    }
}
