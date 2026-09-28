<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SocialMediaSettingsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true, 'is_superadmin' => true, 'is_active' => true]);
    }

    public function test_official_defaults_appear_in_footer_and_contact(): void
    {
        SiteSetting::set('social_instagram', 'https://www.instagram.com/smktahfizhalfatihpku/', 'string', 'social');
        SiteSetting::set('social_facebook', 'https://www.facebook.com/people/SmkTahfizh-AlFatih', 'string', 'social');
        SiteSetting::set('social_youtube', 'https://www.youtube.com/@SMKTAHFIZHALFATIH', 'string', 'social');

        $home = $this->get(route('home'))->assertOk();
        $home->assertSee('https://www.instagram.com/smktahfizhalfatihpku/', false);
        $home->assertSee('https://www.facebook.com/people/SmkTahfizh-AlFatih', false);
        $home->assertSee('https://www.youtube.com/@SMKTAHFIZHALFATIH', false);
        $home->assertSee('Ikuti SMK Tahfizh Al-Fatih', false);

        $contact = $this->get(route('contact.index'))->assertOk();
        $contact->assertSee('https://www.instagram.com/smktahfizhalfatihpku/', false);
    }

    public function test_admin_can_update_and_empty_hides_platform(): void
    {
        $admin = $this->admin();
        SiteSetting::set('social_instagram', 'https://www.instagram.com/smktahfizhalfatihpku/', 'string', 'social');
        SiteSetting::set('social_facebook', 'https://www.facebook.com/people/SmkTahfizh-AlFatih', 'string', 'social');
        SiteSetting::set('social_youtube', 'https://www.youtube.com/@SMKTAHFIZHALFATIH', 'string', 'social');

        // Ubah Instagram.
        $this->actingAs($admin)->put(route('admin.settings.update'), [
            'social_instagram' => 'https://www.instagram.com/sekolahbaru/',
        ])->assertRedirect();
        $this->assertEquals('https://www.instagram.com/sekolahbaru/', SiteSetting::get('social_instagram'));

        // Publik langsung berubah tanpa cache:clear.
        $this->get(route('home'))->assertSee('https://www.instagram.com/sekolahbaru/', false);

        // Kosongkan Facebook => hilang dari publik, tanpa href="#".
        $this->actingAs($admin)->put(route('admin.settings.update'), [
            'social_facebook' => '',
        ])->assertRedirect();
        $page = $this->get(route('home'))->assertOk();
        $page->assertDontSee('https://www.facebook.com/people/SmkTahfizh-AlFatih', false);
        $page->assertDontSee('href="#"', false);
    }

    public function test_invalid_social_url_rejected_with_human_message(): void
    {
        $admin = $this->admin();
        $response = $this->actingAs($admin)->put(route('admin.settings.update'), [
            'social_instagram' => 'https://tiktok.com/@salah',
        ]);
        $response->assertSessionHasErrors(['social_instagram']);
        $errors = session('errors')->get('social_instagram');
        $this->assertStringNotContainsString('validation.url', $errors[0]);
        $this->assertStringContainsString('Instagram', $errors[0]);
    }

    public function test_no_hardcoded_duplicate_social_urls_in_templates(): void
    {
        // Footer + kontak + komponen harus membaca dari SiteSetting, bukan hardcode per-template.
        $footer = file_get_contents(base_path('resources/views/partials/footer.blade.php'));
        $this->assertStringNotContainsString('instagram.com/smktahfizhalfatihpku', $footer, 'Footer tidak boleh hardcode URL; pakai komponen Site Settings.');
        $this->assertStringContainsString('social-links', $footer);

        $contact = file_get_contents(base_path('resources/views/public/contact/index.blade.php'));
        $this->assertStringContainsString('social-links', $contact);

        $component = file_get_contents(base_path('resources/views/components/social-links.blade.php'));
        $this->assertStringContainsString("SiteSetting::get('social_instagram'", $component);
        $this->assertStringContainsString("SiteSetting::get('social_facebook'", $component);
        $this->assertStringContainsString("SiteSetting::get('social_youtube'", $component);
        $this->assertStringContainsString('target="_blank"', $component);
        $this->assertStringContainsString('rel="noopener noreferrer"', $component);
    }
}
