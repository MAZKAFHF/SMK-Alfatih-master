<?php

namespace Tests\Feature;

use App\Models\News;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_home_has_complete_search_and_social_metadata(): void
    {
        $response = $this->get('/?utm_source=test');

        $response->assertOk()
            ->assertSee('<title>SMK Tahfizh Al-Fatih Pekanbaru | SMK Islam &amp; PPDB</title>', false)
            ->assertSee('<link rel="canonical" href="http://127.0.0.1:8000">', false)
            ->assertSee('name="robots" content="index, follow, max-image-preview:large', false)
            ->assertSee('property="og:locale" content="id_ID"', false)
            ->assertSee('name="twitter:card" content="summary_large_image"', false)
            ->assertSee('"@type":"HighSchool"', false)
            ->assertSee('"@type":"WebSite"', false);
    }

    public function test_paginated_public_page_keeps_page_in_canonical_but_drops_tracking(): void
    {
        $this->get('/berita?page=2&utm_campaign=test')
            ->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, follow, noarchive')
            ->assertSee('name="robots" content="noindex, follow, noarchive"', false)
            ->assertSee('<link rel="canonical" href="http://127.0.0.1:8000/berita?page=2">', false);
    }

    public function test_news_detail_has_article_and_breadcrumb_schema(): void
    {
        $news = News::factory()->create([
            'title' => 'Prestasi Siswa Al-Fatih',
            'published_at' => now()->subDay(),
        ]);

        $this->get(route('news.show', $news))
            ->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, follow, noarchive')
            ->assertSee('property="og:type" content="article"', false)
            ->assertSee('property="article:published_time"', false)
            ->assertDontSee('application/ld+json', false);
    }

    public function test_private_and_error_pages_are_not_indexable(): void
    {
        $this->get('/admin/login')
            ->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive, nosnippet')
            ->assertSee('name="robots" content="noindex, nofollow, noarchive, nosnippet"', false)
            ->assertDontSee('rel="canonical"', false)
            ->assertDontSee('property="og:url"', false)
            ->assertDontSee('application/ld+json', false);

        $this->get('/halaman-yang-tidak-ada')
            ->assertNotFound()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive, nosnippet');
    }

    public function test_robots_and_sitemap_are_machine_readable(): void
    {
        $robots = $this->get('/robots.txt');
        $robots->assertOk()->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
        $robots->assertHeaderMissing('Set-Cookie');
        $this->assertStringContainsString('Disallow: /', $robots->getContent());

        $sitemap = $this->get('/sitemap.xml');
        $sitemap->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $sitemap->assertHeaderMissing('Set-Cookie');
        $this->assertNotFalse(simplexml_load_string($sitemap->getContent()));
        $xml = simplexml_load_string($sitemap->getContent());
        $this->assertCount(1, $xml->url);
        $this->assertSame('http://127.0.0.1:8000', (string) $xml->url[0]->loc);
        $this->assertStringNotContainsString('/berita', $sitemap->getContent());
        $this->assertStringNotContainsString('/profil', $sitemap->getContent());
        $this->assertStringNotContainsString('/admin', $sitemap->getContent());
        $this->assertStringNotContainsString('/portal', $sitemap->getContent());
    }
}
