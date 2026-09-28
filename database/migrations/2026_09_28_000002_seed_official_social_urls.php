<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Owner-approved official social URLs sebagai default Site Settings.
     * Tidak menimpa nilai yang sudah diubah admin.
     */
    public function up(): void
    {
        $defaults = [
            'social_instagram' => 'https://www.instagram.com/smktahfizhalfatihpku/',
            'social_facebook' => 'https://www.facebook.com/people/SmkTahfizh-AlFatih',
            'social_youtube' => 'https://www.youtube.com/@SMKTAHFIZHALFATIH',
        ];

        foreach ($defaults as $key => $url) {
            $exists = DB::table('site_settings')->where('key', $key)->exists();
            if (! $exists) {
                DB::table('site_settings')->insert([
                    'key' => $key,
                    'value' => $url,
                    'type' => 'string',
                    'group' => 'social',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // Jangan hapus data admin saat rollback; biarkan nilai tetap.
    }
};
