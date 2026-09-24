<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\Gallery;
use App\Models\News;
use App\Models\Page;
use App\Models\Program;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_returns_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertOk();
    }

    public function test_static_pages_return_successful_response(): void
    {
        foreach (['/ppdb', '/ppdb/status', '/kontak', '/galeri', '/pengumuman'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_program_pages_return_successful_response(): void
    {
        Program::factory()->create(['status' => 'active']);

        $this->get('/program-keahlian')->assertOk();
        $this->get('/program-keahlian/'.Program::first()->slug)->assertOk();
    }

    public function test_news_pages_return_successful_response(): void
    {
        News::factory()->create(['status' => 'published']);

        $this->get('/berita')->assertOk();
        $this->get('/berita/'.News::first()->slug)->assertOk();
    }

    public function test_pages_show_returns_successful_response(): void
    {
        Page::factory()->create(['status' => 'published']);

        $this->get('/'.Page::first()->slug)->assertOk();
    }

    public function test_announcement_and_gallery_pages_return_successful_response(): void
    {
        Announcement::factory()->create(['status' => 'published']);
        Gallery::factory()->create(['status' => 'published']);

        $this->get('/pengumuman')->assertOk();
        $this->get('/galeri')->assertOk();
    }

    public function test_contact_form_can_be_submitted(): void
    {
        $response = $this->post('/kontak', [
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'phone' => '081234567890',
            'subject' => 'Informasi PPDB',
            'message' => 'Saya ingin bertanya mengenai jadwal pendaftaran.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('contact_messages', ['email' => 'budi@example.com']);
    }

    public function test_contact_form_validation_requires_fields(): void
    {
        $this->post('/kontak', [])
            ->assertSessionHasErrors(['name', 'email', 'subject', 'message']);
    }
}
