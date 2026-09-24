# SMK Tahfizh Al-Fatih — Website Sekolah

Website resmi SMK Tahfizh Al-Fatih, sekolah menengah kejuruan berbasis tahfizh Al-Qur'an. Dibangun dengan Laravel 12 dan Tailwind CSS, berisi halaman publik (profil sekolah, program keahlian, berita, galeri, pengumuman, kontak, PPDB online) serta panel admin untuk mengelola pendaftaran peserta didik baru.

## Fitur

### Halaman Publik
- **Beranda** — hero, statistik, program keahlian, berita terbaru, pengumuman, dan galeri
- **Program Keahlian** — PPLG, Multimedia, DKV, TJKT (dengan halaman detail)
- **Berita** — daftar dan detail artikel dengan penjadwalan publikasi
- **Galeri** — galeri foto dengan filter kategori dan lightbox
- **Pengumuman** — daftar pengumuman resmi sekolah
- **Halaman Statis** — profil, sejarah, visi-misi, sambutan kepala sekolah, fasilitas (dinamis dari database)
- **Kontak** — form pesan dengan proteksi throttle
- **PPDB Online** — form pendaftaran peserta didik baru dan cek status pendaftaran berdasarkan nomor registrasi

### Panel Admin (`/admin`)
- Autentikasi admin dengan log aktivitas login/logout
- Dashboard statistik: total pendaftar per status, tren 7 hari, distribusi program, log login terbaru
- Manajemen pendaftaran PPDB: filter, pencarian, ubah status (pending/accepted/rejected/cancelled), hapus
- Manajemen pengguna admin (khusus superadmin)
- Log login (khusus superadmin)

## Teknologi

- **Laravel 12** (PHP ^8.2)
- **Tailwind CSS v4** + Vite 7
- **SQLite** (default) — mendukung MySQL, PostgreSQL, dsb.
- Komponen Blade (`<x-ui.*>`) — design system sendiri
- Vanilla JavaScript untuk interaksi (dropdown, modal, toast, lightbox, dark mode)

## Persyaratan

- PHP >= 8.2
- Composer
- Node.js + npm
- Ekstensi PHP: pdo, mbstring, fileinfo (sesuai kebutuhan Laravel)

## Instalasi

```bash
# 1. Clone repositori
git clone https://github.com/YogaRmdn/SMK-Alfatih.git
cd SMK-Alfatih

# 2. Install dependency PHP
composer install

# 3. Buat file .env
cp .env.example .env

# 4. Generate app key
php artisan key:generate

# 5. Buat database (untuk SQLite)
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"

# 6. Jalankan migrasi dan seeder
php artisan migrate --seed

# 7. Install dan build asset frontend
npm install
npm run build

# 8. Jalankan server
php artisan serve
```

Atau langsung dengan perintah setup bawaan:

```bash
composer setup
php artisan migrate --seed
npm run dev
```

### Storage link (untuk upload gambar)

```bash
php artisan storage:link
```

## Akun Demo Admin

Seeder membuat akun admin superadmin:

| Field    | Nilai                     |
|----------|---------------------------|
| Email    | `admin@smkalfatih.sch.id` |
| Password | `admin1234`               |

> Ubah password setelah login pada produksi.

## Menjalankan Test

```bash
composer test
# atau
php artisan test
```

Test mencakup otorisasi admin, autentikasi, manajemen pendaftaran PPDB, user management, log login, dan halaman publik.

## Struktur Proyek

```
app/
├── Enums/                    # ContentStatus, ProgramStatus, RegistrationStatus
├── Http/
│   ├── Controllers/
│   │   ├── Admin/            # Auth, Dashboard, Registration, UserManagement, LoginLog
│   │   └── Public/           # Home, Program, News, Gallery, Announcement, Contact, PPDB, Page
│   ├── Middleware/           # EnsureUserIsAdmin, EnsureUserIsSuperAdmin
│   └── Requests/             # FormRequest validasi
├── Models/                   # User, Program, News, Page, Announcement, Gallery, dll.
database/
├── migrations/               # 10 tabel
├── factories/
└── seeders/
resources/
├── css/app.css               # Design token & custom styles
├── js/                       # Interaksi vanilla JS
└── views/
    ├── components/           # Design system (<x-ui.*>)
    ├── public/               # Halaman website publik
    ├── admin/                # Panel admin
    └── partials/             # Navbar, footer
routes/
└── web.php                   # Route publik + admin
tests/
└── Feature/                  # Test fitur
```

## Lisensi

Proyek ini bersifat open source di bawah [MIT License](https://opensource.org/licenses/MIT) — framework dasar [Laravel](https://laravel.com) yang juga berlisensi MIT.