<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SettingsPagesTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    public function test_settings_shows_konten_halaman_tab(): void
    {
        $response = $this->actingAs($this->admin())->get(route('admin.settings.index'));
        $response->assertOk();
        $response->assertSee('Konten Halaman');
        $response->assertSee('Profil');
        $response->assertSee('Sejarah');
        $response->assertSee('Visi');
    }

    public function test_admin_can_update_profil_via_page_route_from_settings(): void
    {
        $admin = $this->admin();
        $page = Page::where('slug', 'profil')->first();
        if (! $page) {
            $page = Page::factory()->create(['slug' => 'profil', 'title' => 'Profil Sekolah', 'status' => 'published']);
        }
        $this->assertNotNull($page);

        Cache::put('nav_pages', collect([]), 3600);
        $this->assertTrue(Cache::has('nav_pages'));

        $response = $this->actingAs($admin)->put(route('admin.pages.update', $page), [
            'title' => 'Profil Sekolah Updated via Pengaturan',
            'slug' => 'profil',
            'content' => '<p>Konten baru dari pengaturan</p>',
            'status' => 'published',
            'order' => 1,
        ]);
        $response->assertRedirect(route('admin.pages.index'));

        $this->assertDatabaseHas('pages', ['slug' => 'profil', 'title' => 'Profil Sekolah Updated via Pengaturan']);
        $this->assertFalse(Cache::has('nav_pages'), 'nav_pages cache should be invalidated');
        $this->get('/profil')->assertOk()->assertSee('Konten baru dari pengaturan');
    }

    public function test_guest_cannot_access_settings(): void
    {
        $this->get(route('admin.settings.index'))->assertRedirect(route('admin.login'));
    }
}
