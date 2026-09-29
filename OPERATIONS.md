# Panduan Operasional Admin

## Rutinitas harian

1. Buka **Antrean Kerja**.
2. Tinjau pendaftar yang menunggu verifikasi dan validasi setiap dokumen.
3. Proses permintaan pindah jadwal dan wawancara yang sudah jatuh tempo.
4. Tindak lanjuti pesan masuk; catat kanal respons dan ubah menjadi **Selesai**.
5. Periksa email gagal dan kirim ulang dari detail pendaftar setelah konfigurasi email dipastikan sehat.

## Perubahan data dan penghapusan

- Tidak ada aksi hapus massal pendaftar.
- Hapus biasa memindahkan data ke Trash dan dapat dipulihkan.
- Hapus permanen hanya dilakukan superadmin, satu data pada satu waktu, setelah memastikan backup sehat dan kebutuhan penyimpanan data telah disetujui.
- Penghapusan permanen pendaftar juga menghapus foto serta seluruh versi dokumen privat terkait.

## Pemeriksaan sistem

```bash
php artisan app:backup-verify
php artisan app:media-audit
php artisan about
```

Jika audit media melaporkan berkas hilang, unggah ulang berkas yang sah atau koreksi referensi melalui alur aplikasi. Berkas yatim harus ditinjau sebelum dihapus karena dapat berisi dokumen identitas.

## Setelah pengujian E2E

```bash
php artisan app:cleanup-e2e
php artisan app:cleanup-e2e --force
```

Perintah ini dibatasi pada periode `E2E-*`, domain email uji, aplikasi terkait, pengumuman E2E, serta berkasnya.

## Retensi otomatis

Scheduler menjalankan tugas berikut setelah backup harian:

```text
03:00  audit log dan login log lebih dari 3 hari
03:15  Trash aman lebih dari 7 hari
03:30  lifecycle akun pemohon (hanya jika kebijakan sekolah sudah diaktifkan)
```

Preview tanpa mengubah data:

```bash
php artisan app:retention:logs --dry-run
php artisan app:trash:purge --dry-run
php artisan app:applicants:retire --dry-run
```

`Program` dan `PPDBRegistration` selalu dikecualikan dari purge Trash generik untuk melindungi statistik, riwayat proses, foto, serta dokumen privat. Lifecycle akun pemohon berjalan harian 03:30 bila `APPLICANT_CLEANUP_ENABLED=true`. Tipe orphan/unused/real masing-masing dapat dimatikan via `APPLICANT_ORPHAN_CLEANUP_ENABLED`, `APPLICANT_VERIFIED_UNUSED_CLEANUP_ENABLED`, `APPLICANT_LIFECYCLE_CLEANUP_ENABLED`. Akun real langsung eligible begitu SEMUA periode tertautnya SELESAI (arsip/selesai + hasil dirilis + operasional selesai), status terminal, dan tidak ada tunggakan — tanpa masa tunggu tambahan. `account_retention_until` yang dihitung sistem bersifat info audit. `closes_at` lewat saja TIDAK menghapus akun.

## Menyelesaikan periode PPDB

Alur: `open` → `Tutup` (pendaftaran berhenti, akun tetap aktif) → `Selesaikan` (superadmin, tombol di Periode PPDB).

Menandai Selesai mensyaratkan: tidak ada verifikasi/perbaikan tertunda, tidak ada wawancara/keputusan tertunda, tidak ada koreksi/reschedule pending, dan semua keputusan sudah dirilis. Draf yang tidak pernah dikirim tidak menghalangi. Saat dikonfirmasi, sistem mengunci mutasi operasional periode (verifikasi, booking, wawancara, keputusan ditolak) dan langsung menjalankan pembersihan akun eligible; scheduler harian mengejar sisanya. Membuka kembali (superadmin) adalah jalan keluar bila terjadi kesalahan.

Pada shared hosting, jalankan `php artisan schedule:run` setiap menit. Periksa hasil dengan `php artisan schedule:list`, dan selalu verifikasi backup sebelum perubahan kebijakan retention.
