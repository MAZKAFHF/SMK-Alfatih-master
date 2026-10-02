<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const NEW_TITLE = 'SMK Tahfizh Al-Fatih Pekanbaru | SMK Islam & PPDB';

    private const NEW_DESCRIPTION = 'Website resmi SMK Tahfizh Al-Fatih Pekanbaru, SMK Islam berbasis tahfizh dengan program PPLG, Multimedia, DKV, dan TJKT serta informasi PPDB.';

    public function up(): void
    {
        foreach ([
            'seo_title' => self::NEW_TITLE,
            'seo_description' => self::NEW_DESCRIPTION,
        ] as $key => $value) {
            if (DB::table('site_settings')->where('key', $key)->exists()) {
                DB::table('site_settings')->where('key', $key)->update([
                    'value' => $value,
                    'type' => 'string',
                    'group' => 'seo',
                    'updated_at' => now(),
                ]);

                continue;
            }

            DB::table('site_settings')->insert([
                'key' => $key,
                'value' => $value,
                'type' => 'string',
                'group' => 'seo',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        $previous = [
            'seo_title' => 'SMK Tahfizh Al-Fatih | Sekolah Kejuruan Berbasis Tahfizh',
            'seo_description' => 'Website resmi SMK Tahfizh Al-Fatih Pekanbaru. Temukan profil sekolah, program keahlian, berita, kegiatan, dan informasi PPDB.',
        ];

        foreach ($previous as $key => $value) {
            DB::table('site_settings')
                ->where('key', $key)
                ->update(['value' => $value, 'updated_at' => now()]);
        }
    }
};
