<?php

namespace Tests\Feature;

use App\Models\PpdbPeriod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * ALFATIH//FUTURE motion system regression (static, no browser needed):
 * - public pages render with motion hooks intact
 * - content is NOT hidden when JS is absent (html.js gate)
 * - reduced-motion kill-switch covers every new primitive
 * - legacy hooks relied upon by E2E (data-word-swap, .marquee) still render
 */
class PublicMotionTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_renders_motion_hooks(): void
    {
        $page = $this->get(route('home'))->assertOk();
        $page->assertSee('data-hero', false);
        $page->assertSee('data-navbar-logo', false);
        $page->assertSee('rounded-xl bg-white p-1', false);
        $page->assertSee('data-footer-logo', false);
        $page->assertSee('data-mask-line', false);
        $page->assertSee('data-word-swap', false);
        $page->assertSee('data-magnetic', false);
        $page->assertSee('data-journey="x"', false);
        $page->assertSee('scroll-cue', false);
        $page->assertSee('marquee', false);
    }

    public function test_theme_toggle_has_accessible_name_without_sticky_tooltip(): void
    {
        $page = $this->get(route('home'))->assertOk();
        $page->assertSee('aria-label="Ganti tema terang/gelap"', false);
        $page->assertDontSee('data-ctl-tooltip="Ganti tema terang/gelap"', false);
    }

    public function test_ppdb_journey_and_completed_calm_state(): void
    {
        $this->get(route('ppdb.index'))->assertOk()->assertSee('data-journey', false);

        PpdbPeriod::where('academic_year', '2026/2027')->update(['status' => 'closed', 'is_active' => false, 'is_open' => false]);
        $period = PpdbPeriod::create([
            'academic_year' => '2090/2091', 'status' => 'completed', 'is_active' => false,
            'results_released_at' => now()->subDays(30),
            'operational_completed_at' => now()->subDays(20),
            'account_retention_until' => now()->subDay(),
        ]);
        $page = $this->get(route('ppdb.index'))->assertOk();
        $page->assertSee('Telah Selesai', false);
        // Completed state: no animated registration CTA rendered at all.
        $page->assertDontSee('Buat Akun Portal', false);
        $page->assertDontSee('Masuk Portal', false);
        $page->assertDontSee('data-magnetic', false);
    }

    public function test_news_detail_has_reading_progress_and_article(): void
    {
        $news = \App\Models\News::factory()->create(['status' => 'published', 'published_at' => now()]);
        $page = $this->get(route('news.show', $news))->assertOk();
        $page->assertSee('data-reading-progress', false);
        $page->assertSee('data-reading-article', false);
    }

    public function test_gallery_touch_captions_and_stagger(): void
    {
        \App\Models\Gallery::factory()->create();
        $page = $this->get(route('gallery.index'))->assertOk();
        $html = $page->getContent();
        // Captions visible by default (touch), hover-only on md+.
        $this->assertStringContainsString('md:opacity-0', $html);
        $this->assertStringContainsString('data-stagger', $html);
    }

    public function test_no_js_content_not_hidden_by_css(): void
    {
        $css = file_get_contents(base_path('resources/css/app.css'));
        // Initial-hidden pre-states must be scoped under html.js ...
        $this->assertStringContainsString('html.js .reveal', $css);
        $this->assertStringContainsString('html.js [data-reveal]', $css);
        $this->assertStringContainsString('html.js [data-hero-item]', $css);
        // ... and there must be no unscoped rule hiding these hooks.
        $this->assertDoesNotMatchRegularExpression('/(?<!\.js )\.reveal\s*\{[^}]*opacity:\s*0/', $css);
        $this->assertDoesNotMatchRegularExpression('/(?<!js )\[(data-reveal|data-hero-item)\]\s*\{[^}]*opacity:\s*0/', $css);
    }

    public function test_reduced_motion_covers_all_primitives(): void
    {
        $css = file_get_contents(base_path('resources/css/app.css'));
        $block = strstr($css, '@media (prefers-reduced-motion: reduce)');
        $this->assertNotFalse($block);
        foreach (['data-reveal', 'data-stagger-item', 'data-hero-item', 'data-mask-line', 'data-media-reveal', 'data-build-stage', '.marquee', '.reveal', 'navbar-hidden', 'data-tilt', 'data-magnetic', 'footer-glow'] as $hook) {
            $this->assertStringContainsString($hook, $block, "reduced-motion must neutralize {$hook}");
        }

        $js = file_get_contents(base_path('resources/js/motion.js'));
        $this->assertStringContainsString('prefers-reduced-motion', $js);
        $this->assertStringContainsString('pointer: fine', $js);

    }

    public function test_motion_hub_imported_and_single_system(): void
    {
        $app = file_get_contents(base_path('resources/js/app.js'));
        $this->assertStringContainsString("import './motion'", $app);
        // One coherent system: no AOS/GSAP/Framer stacking.
        $package = file_get_contents(base_path('package.json'));
        foreach (['aos', 'gsap', 'framer-motion', 'animejs', 'motion-one'] as $lib) {
            $this->assertStringNotContainsStringIgnoringCase($lib, $package);
        }
        // Motion hub must fail safe (never break page).
        $js = file_get_contents(base_path('resources/js/motion.js'));
        $this->assertStringContainsString('[motion]', $js);
    }

    public function test_page_hero_renders_all_variants_with_video_contract(): void
    {
        foreach (['cinematic', 'editorial', 'manifesto', 'portrait', 'media', 'tech', 'visual', 'info', 'minimal', 'story'] as $variant) {
            $html = \Illuminate\Support\Facades\Blade::render(
                '<x-page-hero variant="'.$variant.'" eyebrow="Eyebrow" title="Judul Hero" description="Deskripsi." />'
            );
            $this->assertStringContainsString('Judul Hero', $html);
            $this->assertStringContainsString('data-hero', $html);
            $this->assertStringContainsString('data-mask-line', $html);
        }
        // Video drop-in contract: same frame, no structural change.
        $video = \Illuminate\Support\Facades\Blade::render(
            '<x-page-hero variant="cinematic" title="T" :video="[\'src\' => \'/v.mp4\', \'poster\' => \'/p.jpg\']" />'
        );
        $this->assertStringContainsString('<video', $video);
        $this->assertStringContainsString('playsinline', $video);
        $this->assertStringContainsString('preload="metadata"', $video);
        $this->assertStringContainsString('muted', $video);
        // Motion tokens present.
        $css = file_get_contents(base_path('resources/css/app.css'));
        foreach (['--motion-micro', '--motion-fast', '--motion-interaction', '--motion-normal', '--motion-section', '--motion-cinematic'] as $token) {
            $this->assertStringContainsString($token, $css);
        }
        $this->assertStringContainsString('data-page-enter', $css);
        $this->assertStringContainsString('.btn-arrow', $css);
        $this->assertStringContainsString('.btn-sweep', $css);
        $js = file_get_contents(base_path('resources/js/motion.js'));
        $this->assertStringContainsString('initPageEnter', $js);
        $this->assertStringContainsString('indexMaskGroups', $js);
    }

    public function test_programs_gallery_contact_hooks(): void
    {
        \App\Models\Program::factory()->create(['status' => 'active']);
        $this->get(route('programs.index'))->assertOk()->assertSee('data-tilt', false);
        $program = \App\Models\Program::factory()->create(['status' => 'active']);
        $this->get(route('programs.show', $program))->assertOk()->assertSee('data-media', false);
        $this->get(route('contact.index'))->assertOk()->assertSee('data-stagger', false);
        $this->get(route('announcements.index'))->assertOk()->assertSee('data-stagger', false);
        $this->get(route('news.index'))->assertOk()->assertSee('data-stagger', false);
    }
}
