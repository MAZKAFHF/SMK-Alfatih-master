# Peta Sistem SMK Tahfizh Al-Fatih

Dokumen ini menggambarkan sistem saat ini. Detail operasi ada di `OPERATIONS.md`; prosedur backup ada di `BACKUP_RESTORE.md`.

## Area pengguna

- Website publik: profil, program, berita, galeri, pengumuman, halaman sekolah, dan kontak.
- Portal PPDB privat: akun orang tua, beberapa calon siswa per akun, draf, unggah dokumen privat, pengiriman final, verifikasi, pemilihan wawancara, dan hasil.
- Admin: dashboard per periode, antrean kerja, pendaftar, review dokumen, wawancara, keputusan, CMS, pesan masuk, trash, dan audit.
- Superadmin: pengelolaan akun admin, log login, serta penghapusan permanen per data dari Trash.

Tidak tersedia lagi formulir atau pelacak PPDB publik berbasis nomor. Data pendaftar hanya dapat diakses melalui akun pemohon yang memiliki data atau akun admin.

## Alur PPDB

`draft → submitted/resubmitted → verified/waiting_slot → scheduled → waiting_decision → passed/not_passed`

- Pengiriman final memerlukan email terverifikasi, data wajib lengkap, dan lima dokumen wajib.
- Dokumen disimpan pada disk `ppdb_private`; akses preview selalu melewati otorisasi aplikasi.
- Hasil belum terlihat oleh pemohon sampai admin merilis keputusan.
- Semua perubahan penting memiliki riwayat status dan/atau audit log.

## Komponen utama

- `app/Http/Controllers/Portal`: autentikasi dan alur pemohon.
- `app/Http/Controllers/Admin`: operasi admin, CMS, komunikasi, dan antrean kerja.
- `app/Services`: aturan bisnis PPDB, dokumen, email, slot, keputusan, media, serta audit.
- `app/Console/Commands`: backup, verifikasi backup, audit media, dan pembersihan data E2E.
- `resources/views`: Blade untuk publik, portal, admin, dan komponen UI.
- `tests/Feature` dan `e2e`: perlindungan backend dan browser.

## Penyimpanan

- Database: SQLite untuk lokal; MySQL didukung untuk produksi.
- Media publik: `storage/app/public`.
- Dokumen PPDB privat: `storage/app/private/ppdb`.
- Backup: lokasi dari `APP_BACKUP_DIR`, di luar web root.

## Batas keputusan sekolah

Kebijakan privasi dan retensi data belum ditetapkan dalam kode karena menunggu keputusan resmi sekolah. Jangan mengaktifkan penghapusan otomatis berbasis umur sebelum masa simpan, dasar pemrosesan, pemilik proses, dan prosedur permintaan subjek data disahkan.

Fitur yang sengaja belum menjadi ruang lingkup saat ini: pemisahan role lebih rinci, preview draf CMS, 2FA, dan audit aksesibilitas otomatis.
