<?php

use App\Models\SiteSetting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        SiteSetting::set('seo_title', 'SMK Tahfizh Al-Fatih Pekanbaru', 'string', 'seo');
        SiteSetting::set(
            'seo_description',
            "Website resmi SMK Tahfizh Al-Fatih Pekanbaru, sekolah menengah kejuruan berbasis tahfizh Al-Qur'an. Informasi jurusan PPLG dan TJKT, fasilitas, serta PPDB online.",
            'string',
            'seo'
        );
    }

    public function down(): void
    {
        SiteSetting::set('seo_title', 'SMK Tahfizh Al-Fatih Pekanbaru | SMK Islam PKU', 'string', 'seo');
        SiteSetting::set(
            'seo_description',
            "Website resmi SMK Tahfizh Al-Fatih Pekanbaru (PKU), SMK Islam berbasis Al-Qur'an. Informasi jurusan PPLG dan TJKT, tahfizh, fasilitas, serta PPDB online.",
            'string',
            'seo'
        );
    }
};
