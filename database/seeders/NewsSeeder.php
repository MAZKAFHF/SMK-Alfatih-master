<?php

namespace Database\Seeders;

use App\Models\News;
use Illuminate\Database\Seeder;

class NewsSeeder extends Seeder
{
    public function run(): void
    {
        $news = [
            [
                'title' => 'Pembukaan PPDB SMK Tahfizh Al-Fatih Tahun Ajaran 2026/2027',
                'slug' => 'pembukaan-ppdb-2026-2027',
                'content' => '<p>SMK Tahfizh Al-Fatih resmi membuka Penerimaan Peserta Didik Baru (PPDB) untuk tahun ajaran 2026/2027. Pendaftaran dapat dilakukan secara online melalui website resmi sekolah.</p><p>Calon peserta didik dapat memilih program keahlian sesuai minat dan bakatnya. Persiapkan dokumen yang diperlukan dan ikuti seluruh alur pendaftaran hingga selesai.</p>',
                'status' => 'published',
                'published_at' => now()->subDays(12),
            ],
            [
                'title' => 'Siswa SMK Tahfizh Al-Fatih Raih Juara LKS Bidang Desain Grafis',
                'slug' => 'siswa-raih-juara-lks-desain-grafis',
                'content' => '<p>Prestasi membanggakan kembali ditorehkan siswa SMK Tahfizh Al-Fatih. Salah satu siswa program keahlian Multimedia berhasil meraih juara pada Lomba Kompetensi Siswa (LKS) tingkat provinsi bidang desain grafis.</p><p>Keberhasilan ini membuktikan bahwa siswa kami mampu bersaing di kancah yang lebih luas. Kami berharap prestasi ini menjadi penyemangat bagi seluruh siswa lainnya.</p>',
                'status' => 'published',
                'published_at' => now()->subDays(5),
            ],
            [
                'title' => 'Program Tahfizh Masuk dalam Kurikulum Harian',
                'slug' => 'program-tahfizh-kurikulum-harian',
                'content' => '<p>SMK Tahfizh Al-Fatih mengintegrasikan program tahfizh Al-Qur\'an ke dalam jadwal harian pembelajaran. Setiap siswa mendapatkan bimbingan hafalan yang terstruktur dengan target yang jelas.</p><p>Pembinaan dilakukan oleh guru tahfizh bersanad dan menggunakan metode yang menyenangkan, sehingga siswa mampu menyeimbangkan antara kompetensi vokasi dan hafalan Al-Qur\'an.</p>',
                'status' => 'published',
                'published_at' => now()->subDays(18),
            ],
            [
                'title' => 'Kegiatan Bakti Sosial dan Santunan Anak Yatim',
                'slug' => 'bakti-sosial-santunan-anak-yatim',
                'content' => '<p>Sebagai wujud kepedulian sosial, SMK Tahfizh Al-Fatih menggelar kegiatan bakti sosial dan santunan anak yatim. Kegiatan ini melibatkan seluruh siswa dan guru.</p><p>Melalui kegiatan ini, kami berharap tertanam nilai empati dan kepedulian pada diri setiap peserta didik, sejalan dengan visi mencetak generasi berakhlakul karimah.</p>',
                'status' => 'published',
                'published_at' => now()->subDays(25),
            ],
            [
                'title' => 'Kerja Sama Industri dan Dunia Kerja untuk Praktik Kerja Lapangan',
                'slug' => 'kerja-sama-industri-pkl',
                'content' => '<p>SMK Tahfizh Al-Fatih menjalin kerja sama dengan berbagai perusahaan dan dunia industri untuk mendukung program Praktik Kerja Lapangan (PKL) siswa.</p><p>Kerja sama ini memberikan kesempatan bagi siswa untuk merasakan dunia kerja secara langsung, sekaligus membangun jejaring yang berguna setelah lulus.</p>',
                'status' => 'published',
                'published_at' => now()->subDays(30),
            ],
            [
                'title' => 'Kegiatan Masa Pengenalan Lingkungan Sekolah',
                'slug' => 'kegiatan-mpls',
                'content' => '<p>Masa Pengenalan Lingkungan Sekolah (MPLS) untuk peserta didik baru berjalan dengan penuh semangat. Berbagai kegiatan pengenalan dan pembinaan karakter diselenggarakan untuk menyambut siswa baru.</p><p>Selamat bergabung kepada seluruh siswa baru. Selamat menempuh pendidikan di SMK Tahfizh Al-Fatih!</p>',
                'status' => 'published',
                'published_at' => now()->subDays(40),
            ],
        ];

        foreach ($news as $item) {
            News::updateOrCreate(['slug' => $item['slug']], $item);
        }
    }
}
