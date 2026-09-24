<?php

namespace Database\Seeders;

use App\Models\Announcement;
use Illuminate\Database\Seeder;

class AnnouncementSeeder extends Seeder
{
    public function run(): void
    {
        $announcements = [
            [
                'title' => 'PPDB Tahun Ajaran 2026/2027 Telah Dibuka',
                'content' => 'Pendaftaran Penerimaan Peserta Didik Baru (PPDB) SMK Tahfizh Al-Fatih tahun ajaran 2026/2027 telah dibuka. Silakan melakukan pendaftaran secara online melalui menu PPDB di website resmi kami. Kuota terbatas, segera daftarkan diri Anda!',
                'status' => 'published',
                'published_at' => now()->subDays(3),
            ],
            [
                'title' => 'Jadwal Ujian Kompetensi Keahlian Semester Genap',
                'content' => 'Ujian Kompetensi Keahlian (UKK) semester genap akan dilaksanakan sesuai jadwal yang telah ditentukan. Siswa diharapkan mempersiapkan diri dengan baik dan mengikuti seluruh rangkaian ujian.',
                'status' => 'published',
                'published_at' => now()->subDays(10),
            ],
            [
                'title' => 'Kegiatan MPLS Siswa Baru',
                'content' => 'Masa Pengenalan Lingkungan Sekolah (MPLS) bagi siswa baru akan dilaksanakan pada awal tahun ajaran. Orang tua dan siswa akan mendapatkan informasi lebih lanjut melalui website dan media sosial resmi sekolah.',
                'status' => 'published',
                'published_at' => now()->subDays(20),
            ],
        ];

        foreach ($announcements as $announcement) {
            Announcement::updateOrCreate(['title' => $announcement['title']], $announcement);
        }
    }
}
