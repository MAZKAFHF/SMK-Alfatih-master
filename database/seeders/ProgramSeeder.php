<?php

namespace Database\Seeders;

use App\Models\Program;
use Illuminate\Database\Seeder;

class ProgramSeeder extends Seeder
{
    public function run(): void
    {
        $programs = [
            [
                'name' => 'PPLG',
                'slug' => 'pplg',
                'short_description' => 'Pengembangan Perangkat Lunak dan Gim. Belajar coding, membangun aplikasi web, mobile, dan game.',
                'description' => 'Kompetensi Keahlian Pengembangan Perangkat Lunak dan Gim (PPLG) membekali siswa dengan keterampilan merancang, membangun, dan mengelola perangkat lunak. Siswa belajar pemrograman web, mobile, database, UI/UX design, serta pengembangan game. Lulusan PPLG siap bekerja sebagai programmer, web developer, mobile developer, hingga game developer.',
                'status' => 'active',
                'order' => 1,
            ],
            [
                'name' => 'Multimedia',
                'slug' => 'multimedia',
                'short_description' => 'Desain grafis, animasi, videografi, dan produksi media kreatif.',
                'description' => 'Kompetensi Keahlian Multimedia mempelajari pembuatan konten visual dan audio visual. Siswa dilatih dalam desain grafis, fotografi, videografi, animasi 2D dan 3D, editing video, hingga produksi film pendek. Lulusan Multimedia siap menjadi desainer grafis, video editor, animator, dan content creator.',
                'status' => 'active',
                'order' => 2,
            ],
            [
                'name' => 'DKV',
                'slug' => 'dkv',
                'short_description' => 'Desain Komunikasi Visual: identitas visual, ilustrasi, dan media cetak.',
                'description' => 'Kompetensi Keahlian Desain Komunikasi Visual (DKV) berfokus pada komunikasi melalui elemen visual. Siswa belajar prinsip desain, tipografi, ilustrasi, desain kemasan, branding, dan publikasi digital maupun cetak. Lulusan DKV siap berkarier sebagai desainer grafis, illustrator, dan brand designer.',
                'status' => 'active',
                'order' => 3,
            ],
            [
                'name' => 'TJKT',
                'slug' => 'tjkt',
                'short_description' => 'Teknik Jaringan Komputer dan Telekomunikasi. Ahli instalasi, jaringan, dan keamanan jaringan.',
                'description' => 'Kompetensi Keahlian Teknik Jaringan Komputer dan Telekomunikasi (TJKT) mempelajari instalasi dan perawatan komputer, jaringan lokal (LAN), jaringan nirkabel, hingga administrasi server. Siswa dibekali sertifikasi praktik jaringan modern. Lulusan TJKT siap menjadi teknisi jaringan, network administrator, dan teknisi telekomunikasi.',
                'status' => 'active',
                'order' => 4,
            ],
        ];

        foreach ($programs as $program) {
            Program::updateOrCreate(['slug' => $program['slug']], $program);
        }
    }
}
