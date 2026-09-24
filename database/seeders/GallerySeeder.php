<?php

namespace Database\Seeders;

use App\Models\Gallery;
use Illuminate\Database\Seeder;

class GallerySeeder extends Seeder
{
    public function run(): void
    {
        $galleries = [
            ['title' => 'Kegiatan PPDB 2025/2026', 'category' => 'Kegiatan'],
            ['title' => 'Pembelajaran PPLG', 'category' => 'Kegiatan'],
            ['title' => 'Praktik Multimedia', 'category' => 'Kegiatan'],
            ['title' => 'Laboratorium Komputer', 'category' => 'Fasilitas'],
            ['title' => 'Studio Multimedia', 'category' => 'Fasilitas'],
            ['title' => 'Masjid Sekolah', 'category' => 'Fasilitas'],
            ['title' => 'Juara LKS Desain Grafis', 'category' => 'Prestasi'],
            ['title' => 'Juara Tahfizh', 'category' => 'Prestasi'],
            ['title' => 'Kegiatan Santunan Anak Yatim', 'category' => 'Kegiatan'],
            ['title' => 'Perpustakaan', 'category' => 'Fasilitas'],
            ['title' => 'Praktik Kerja Lapangan', 'category' => 'Kegiatan'],
            ['title' => 'Juara PIK-R', 'category' => 'Prestasi'],
        ];

        $order = 1;
        foreach ($galleries as $gallery) {
            Gallery::updateOrCreate(
                ['title' => $gallery['title']],
                array_merge($gallery, ['status' => 'published', 'order' => $order++, 'image' => null])
            );
        }
    }
}
