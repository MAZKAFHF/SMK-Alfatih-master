<?php

use App\Models\SiteSetting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    private const TITLE = 'SMK Tahfizh Al-Fatih Pekanbaru | SMK Islam PKU';

    private const DESCRIPTION = "Website resmi SMK Tahfizh Al-Fatih Pekanbaru (PKU), SMK Islam berbasis Al-Qur'an. Informasi jurusan PPLG dan TJKT, tahfizh, fasilitas, serta PPDB online.";

    public function up(): void
    {
        SiteSetting::set('seo_title', self::TITLE, 'string', 'seo');
        SiteSetting::set('seo_description', self::DESCRIPTION, 'string', 'seo');
    }

    public function down(): void
    {
        SiteSetting::set('seo_title', 'SMK Tahfizh Al-Fatih Pekanbaru | SMK Islam & PPDB', 'string', 'seo');
        SiteSetting::set(
            'seo_description',
            'Website resmi SMK Tahfizh Al-Fatih Pekanbaru, SMK Islam berbasis tahfizh dengan program PPLG, Multimedia, DKV, dan TJKT serta informasi PPDB.',
            'string',
            'seo'
        );
    }
};
