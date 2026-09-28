# Content Requires Verification — SMK Tahfizh Al-Fatih

> Jangan jadikan nilai placeholder sebagai fakta resmi sebelum dikonfirmasi pihak sekolah.

## Statistik Homepage (SiteSetting `stat_*`)

| Key | Default | Source | Needs Verification |
|-----|---------|--------|-------------------|
| `stat_programs` | `4` | `DatabaseSeeder` 4 program (PPLG, Multimedia, DKV, TJKT) | Verify jumlah program aktif real |
| `stat_founded` | `2016` | Hardcoded di `resources/views/public/home.blade.php` original | Verify tahun berdiri dari akta/dinas |
| `stat_students` | `850+` | Hardcoded `850+` | Verify jumlah siswa aktif terkini (Dapodik) |
| `stat_alumni` | `1200+` | Hardcoded `1200+` | Verify alumni terdata |

Admin dapat hide dengan kosongkan field di **Pengaturan > Homepage**; kosong = tidak tampil.

## Kontak & Alamat

| Field | Default | File |
|-------|---------|------|
| Alamat `Jl. Pendidikan No. 1, Jakarta` | footer + contact page | `resources/views/partials/footer.blade.php:51` , `public/contact/index.blade.php:17` |
| Telepon `(021) 1234-5678` | footer + contact | same |
| Email `info@smkalfatih.sch.id` | footer + contact | same |
| Maps URL | SiteSetting `maps_url` | — |
| WhatsApp `school_whatsapp` | setting | — |

Verifikasi alamat lengkap (kecamatan, kota, kode pos), telepon/wa aktif, email resmi.

## Sekolah Identity

| Key | Default |
|-----|---------|
| `school_name` | `SMK Tahfizh Al-Fatih` |
| `school_tagline` | — (currently empty, can set) |
| `headmaster_name` | — (empty, verify nama Kepala Sekolah) |
| `founding_year` | `2016` (duplicate of stat) |

## Halaman Statis (PageSeeder 5)

- Profil, Sejarah, Visi-Misi, Sambutan Kepala Sekolah, Fasilitas — konten seeded dari `database/seeders/PageSeeder.php` harus direview oleh sekolah untuk akurasi (visi misi tidak boleh diinvent).

## Program Keahlian

- 4 program seeded dengan deskripsi; verifikasi nama resmi sesuai KEMDIKBUD dan status `active` vs `inactive`.

## SEO

- `seo_title` / `seo_description` default dari SiteSetting jika kosong → fallback ke `config('app.name')`; verifikasi tidak misleading.

## PPDB

- Tahun ajaran, jadwal, status, kuota, pengumuman, dan kontak dikelola per baris `ppdb_periods`; verifikasi periode aktif sebelum publikasi.
- `quota`, `announcement`, `contact_info` perlu diisi panitia.

## Media

- `public/img/logo.png` dan `public/img/beranda.png` — verify logo resmi resolusi tinggi; beranda.png currently unused (dead asset) — ganti atau hapus.

## Cara Verifikasi

1. Admin login → Pengaturan → isi dengan data resmi.
2. Simpan, cek homepage/footer langsung berubah (cache 3600 di-flush).
3. Tandai dokumen ini checked: beri tanda ✅ per baris setelah konfirmasi tertulis dari pihak sekolah.
