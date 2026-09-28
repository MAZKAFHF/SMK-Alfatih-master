# Backup & Restore — SMK Tahfizh Al-Fatih

Backup aplikasi mencakup tiga komponen dalam satu set bertimestamp:

- database (`db-*.sqlite.gz` atau `db-*.sql.gz`);
- media publik dan dokumen PPDB privat (`files-*.tar.gz`);
- manifest ukuran dan SHA-256 (`manifest-*.json`).

Dokumen identitas di `storage/app/private/ppdb` wajib diperlakukan sebagai data rahasia. Jangan menyimpan backup di folder publik atau repository.

## Konfigurasi produksi

```dotenv
APP_BACKUP_ENABLED=true
APP_BACKUP_DIR=/mnt/backup-encrypted/smk-alfatih
APP_BACKUP_RETENTION=14
APP_BACKUP_DAILY_AT=02:00
```

Laravel menjadwalkan `app:backup` setiap hari. Server tetap harus menjalankan scheduler Laravel setiap menit:

```cron
* * * * * cd /path/SMK-Alfatih-master && php artisan schedule:run >> /var/log/smk-scheduler.log 2>&1
```

Direktori backup sebaiknya merupakan volume terenkripsi atau volume yang direplikasi ke lokasi lain. Pastikan hanya operator berwenang yang dapat membacanya.

## Operasi

```bash
php artisan app:backup
php artisan app:backup --retention=30
php artisan app:backup-verify
php artisan app:backup-verify manifest-20260928_020000.json
```

Backup belum dianggap sehat sebelum `app:backup-verify` berhasil dan uji pemulihan berkala di staging selesai.

## Restore (selalu uji di staging dahulu)

1. Aktifkan maintenance: `php artisan down`.
2. Verifikasi manifest yang akan dipulihkan.
3. Salin database aktif dan folder storage aktif ke lokasi rollback.
4. Pulihkan database: ekstrak/salin SQLite ke `database/database.sqlite`, atau impor dump MySQL terkompresi.
5. Ekstrak `files-*.tar.gz` ke `storage/app`; arsip berisi `public/` dan `private/ppdb/`.
6. Jalankan `php artisan migrate:status`, `php artisan storage:link`, dan `php artisan cache:clear`.
7. Periksa login admin, satu media publik, dan satu dokumen PPDB privat dengan akun berwenang.
8. Jalankan smoke test, lalu `php artisan up`.

Jangan menguji restore langsung pada database produksi. Catat operator, waktu, manifest, dan hasil uji restore dalam log operasional sekolah.
