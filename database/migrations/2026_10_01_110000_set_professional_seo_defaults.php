<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $defaults = [
            'seo_title' => 'SMK Tahfizh Al-Fatih | Sekolah Kejuruan Berbasis Tahfizh',
            'seo_description' => 'Website resmi SMK Tahfizh Al-Fatih Pekanbaru. Temukan profil sekolah, program keahlian, berita, kegiatan, dan informasi PPDB.',
        ];

        foreach ($defaults as $key => $value) {
            $setting = DB::table('site_settings')->where('key', $key)->first();

            if (! $setting) {
                DB::table('site_settings')->insert([
                    'key' => $key,
                    'value' => $value,
                    'type' => 'string',
                    'group' => 'seo',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                continue;
            }

            if (blank($setting->value)) {
                DB::table('site_settings')->where('key', $key)->update([
                    'value' => $value,
                    'group' => 'seo',
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // Do not remove or overwrite SEO copy that may have been edited by an admin.
    }
};
