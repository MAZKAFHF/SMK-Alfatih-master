<?php

namespace Tests\Feature;

use App\Models\Gallery;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GalleryTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    public function test_gallery_category_filter_server_side(): void
    {
        Gallery::factory()->create(['title' => 'Kegiatan A', 'category' => 'Kegiatan', 'status' => 'published']);
        Gallery::factory()->create(['title' => 'Prestasi B', 'category' => 'Prestasi', 'status' => 'published']);

        $response = $this->get(route('gallery.index', ['category' => 'Kegiatan']));
        $response->assertOk();
        $response->assertSee('Kegiatan A');
        $response->assertDontSee('Prestasi B');

        $response2 = $this->get(route('gallery.index', ['category' => 'Prestasi']));
        $response2->assertSee('Prestasi B');
        $response2->assertDontSee('Kegiatan A');
    }

    public function test_gallery_pagination_preserves_category(): void
    {
        // Create 13 galleries to trigger pagination (12 per page)
        for ($i = 0; $i < 13; $i++) {
            Gallery::factory()->create(['category' => 'Kegiatan', 'status' => 'published', 'title' => "Foto $i"]);
        }
        $response = $this->get(route('gallery.index', ['category' => 'Kegiatan', 'page' => 2]));
        $response->assertOk();
        // Links should preserve category
        $response->assertSee('category=Kegiatan', false);
    }

    public function test_gallery_pagination_works(): void
    {
        for ($i = 0; $i < 13; $i++) {
            Gallery::factory()->create(['status' => 'published']);
        }
        $this->get(route('gallery.index'))->assertOk();
        $this->get(route('gallery.index', ['page' => 2]))->assertOk();
    }
}
