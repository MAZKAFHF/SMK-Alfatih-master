<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_site_settings_validation_and_cache(): void
    {
        $admin = \App\Models\User::factory()->create(['is_admin' => true]);
        $r1 = $this->actingAs($admin)->put(route('admin.settings.update'), [
            'school_name' => 'Test',
            'school_email' => 'not-an-email',
        ]);
        $r1->assertSessionHasErrors('school_email');

        $r2 = $this->actingAs($admin)->put(route('admin.settings.update'), [
            'school_name' => 'Valid',
            'school_email' => 'valid@smk.test',
            'maps_url' => 'not-url',
        ]);
        $r2->assertSessionHasErrors('maps_url');

        $r3 = $this->actingAs($admin)->put(route('admin.settings.update'), [
            'school_name' => 'Valid Name',
            'school_email' => 'valid@smk.test',
        ]);
        $r3->assertRedirect();
        $this->assertEquals('Valid Name', SiteSetting::get('school_name'));
    }
}
