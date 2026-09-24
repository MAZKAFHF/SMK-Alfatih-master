<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            [
                'title' => 'Profil Sekolah',
                'slug' => 'profil',
                'order' => 1,
                'meta_title' => 'Profil SMK Tahfizh Al-Fatih',
                'meta_description' => 'Profil lengkap SMK Tahfizh Al-Fatih, sekolah menengah kejuruan berbasis tahfizh Al-Qur\'an.',
                'content' => '
<h2>Sekolah Menengah Kejuruan Berbasis Tahfizh</h2>
<p>SMK Tahfizh Al-Fatih adalah sekolah menengah kejuruan yang memadukan pendidikan vokasi dengan pembinaan hafalan Al-Qur\'an. Kami percaya bahwa generasi unggul lahir dari kombinasi antara kompetensi profesional dan keimanan yang kuat.</p>
<p>Berdiri dengan komitmen mencetak lulusan yang siap kerja, siap berwirausaha, dan berakhlak mulia, SMK Tahfizh Al-Fatih menghadirkan lingkungan belajar yang modern, islami, dan nyaman bagi setiap peserta didik.</p>
<h3>Identitas Sekolah</h3>
<ul>
<li>Nama: SMK Tahfizh Al-Fatih</li>
<li>Jenjang: Sekolah Menengah Kejuruan</li>
<li>Kurikulum: Kurikulum Merdeka</li>
<li>Akreditasi: A</li>
<li>Waktu Belajar: Full Day School</li>
</ul>
<h3>Keunggulan</h3>
<p>Setiap siswa mendapatkan program tahfizh terstruktur dengan target hafalan, guru pembimbing tahfizh bersanad, pembelajaran berbasis proyek, serta kerjasama industri dan dunia usaha untuk praktik kerja lapangan.</p>
',
            ],
            [
                'title' => 'Sejarah',
                'slug' => 'sejarah',
                'order' => 2,
                'meta_title' => 'Sejarah SMK Tahfizh Al-Fatih',
                'meta_description' => 'Perjalanan dan sejarah berdirinya SMK Tahfizh Al-Fatih.',
                'content' => '
<h2>Perjalanan Kami</h2>
<p>SMK Tahfizh Al-Fatih lahir dari keprihatinan akan semakin sedikitnya lembaga pendidikan yang mampu memadukan keunggulan vokasi dengan nilai-nilai keislaman yang kuat.</p>
<p>Bermula dari musyawarah para pendiri yang ingin menghadirkan sekolah kejuruan dengan ciri khas tahfizh Al-Qur\'an, sekolah ini kemudian dirintis dan mulai menerima peserta didik perdana.</p>
<p>Seiring berjalannya waktu, fasilitas terus dibenahi, tenaga pendidik terus dilatih, dan kerjasama dengan berbagai industri dan pesantren pun diperluas. Kini SMK Tahfizh Al-Fatih terus bertumbuh menjadi pilihan utama orang tua yang menginginkan anaknya berprestasi sekaligus berkarakter islami.</p>
<p>Kami berkomitmen untuk terus berinovasi agar setiap lulusan siap menghadapi tantangan zaman tanpa kehilangan jati dirinya sebagai generasi penghafal Al-Qur\'an.</p>
',
            ],
            [
                'title' => 'Visi & Misi',
                'slug' => 'visi-misi',
                'order' => 3,
                'meta_title' => 'Visi dan Misi SMK Tahfizh Al-Fatih',
                'meta_description' => 'Visi dan misi SMK Tahfizh Al-Fatih dalam mencetak generasi unggul dan berakhlak mulia.',
                'content' => '
<h2>Visi</h2>
<p>Menjadi sekolah menengah kejuruan unggul yang mencetak generasi berprestasi, berjiwa technopreneur, dan berakhlakul karimah dengan berlandaskan Al-Qur\'an.</p>
<h2>Misi</h2>
<ul>
<li>Menyelenggarakan pendidikan vokasi yang relevan dengan kebutuhan industri dan perkembangan teknologi.</li>
<li>Membina hafalan Al-Qur\'an secara terstruktur dan berkesinambungan bagi seluruh peserta didik.</li>
<li>Menanamkan akhlak mulia dan kemandirian melalui pembiasaan dan keteladanan.</li>
<li>Mengembangkan bakat dan minat peserta didik melalui kegiatan intrakurikuler dan ekstrakurikuler.</li>
<li>Menjalin kerjasama dengan dunia usaha dan dunia industri untuk meningkatkan kompetensi lulusan.</li>
</ul>
<h2>Tujuan</h2>
<p>Menghasilkan lulusan yang beriman, berkompeten di bidangnya, siap bekerja, dan mampu melanjutkan ke jenjang pendidikan yang lebih tinggi.</p>
',
            ],
            [
                'title' => 'Sambutan Kepala Sekolah',
                'slug' => 'sambutan-kepala-sekolah',
                'order' => 4,
                'meta_title' => 'Sambutan Kepala SMK Tahfizh Al-Fatih',
                'meta_description' => 'Sambutan Kepala Sekolah SMK Tahfizh Al-Fatih.',
                'content' => '
<h2>Assalamu\'alaikum Warahmatullahi Wabarakatuh</h2>
<p>Alhamdulillah, puji syukur kita panjatkan kehadirat Allah SWT atas segala nikmat dan karunia-Nya. Kami bersyukur SMK Tahfizh Al-Fatih dapat terus hadir memberikan layanan pendidikan terbaik bagi masyarakat.</p>
<p>Kami menyambut dengan hangat setiap calon peserta didik dan orang tua yang ingin menjadi bagian dari keluarga besar SMK Tahfizh Al-Fatih. Di sekolah ini, kami tidak hanya mendidik siswa agar mahir dalam keterampilan kejuruan, tetapi juga membina mereka menjadi generasi yang berakhlak mulia dan mencintai Al-Qur\'an.</p>
<p>Melalui kombinasi kurikulum vokasi yang modern, pembinaan tahfizh yang terstruktur, serta lingkungan yang islami dan menyenangkan, kami berikhtiar mencetak lulusan yang siap bersaing dan bermanfaat bagi umat.</p>
<p>Wassalamu\'alaikum Warahmatullahi Wabarakatuh.</p>
<p><strong>Kepala Sekolah</strong></p>
',
            ],
            [
                'title' => 'Fasilitas',
                'slug' => 'fasilitas',
                'order' => 5,
                'meta_title' => 'Fasilitas SMK Tahfizh Al-Fatih',
                'meta_description' => 'Fasilitas lengkap penunjang kegiatan belajar SMK Tahfizh Al-Fatih.',
                'content' => '
<h2>Fasilitas Penunjang Pembelajaran</h2>
<p>SMK Tahfizh Al-Fatih menyediakan berbagai fasilitas untuk mendukung kenyamanan dan kualitas pembelajaran peserta didik.</p>
<ul>
<li>Ruang kelas ber-AC dengan media pembelajaran modern.</li>
<li>Laboratorium komputer dan jaringan.</li>
<li>Studio multimedia untuk praktik desain, fotografi, dan videografi.</li>
<li>Masjid untuk kegiatan ibadah dan pembinaan tahfizh.</li>
<li>Perpustakaan dengan koleksi buku lengkap.</li>
<li>Lapangan olahraga serbaguna.</li>
<li>Kantin sehat dan ruang unit kesehatan sekolah (UKS).</li>
<li>Area wifi dan lingkungan asri yang aman dan nyaman.</li>
</ul>
',
            ],
        ];

        foreach ($pages as $page) {
            Page::updateOrCreate(['slug' => $page['slug']], $page);
        }
    }
}
