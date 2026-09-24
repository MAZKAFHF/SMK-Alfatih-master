# DATABASE PRODUCTION GUIDE — SMK Tahfizh Al-Fatih

## Overview

- Dev default `sqlite` (file `database/database.sqlite`). Migrasi kompatibel MySQL/PostgreSQL (no raw sqlite-only SQL).
- Production bisa tetap `sqlite` untuk <10k pendaftar & low concurrent, atau `mysql`/`mariadb` untuk concurrency lebih tinggi.
- Config `config/database.php` mendukung ketiga driver.

## SQLite vs MySQL

| Aspek | SQLite | MySQL/MariaDB |
|-------|--------|---------------|
| Concurrency PPDB regnum atomic | Serialisasi via transaction `lockForUpdate` (cukup untuk low traffic) | Row-level lock lebih kuat, retry 3x |
| Backup | Copy file `database.sqlite` | `mysqldump` |
| Hosting | Tanpa service tambahan, cukup writable `database/` | Butuh DB server |
| Limit | ~10k-50k rows per table fine | Scale >100k |
| Pitfall | Jangan letakkan `database.sqlite` di `public/` | Umlaute: ensure `utf8mb4` |

## Migrasi Compatibility

- Semua migrasi pakai `Schema` builder, bukan raw SQL.
- `softDeletes()` , `foreignId()->constrained()->nullOnDelete()` , `index()` kompatibel keduanya.
- Audit `registration_number` generation pakai `lockForUpdate()` + unique check, kompatibel sqlite (serial) & mysql (row lock).
- Tidak ada `->json()` yang khusus MySQL — `audit_logs.old_values` pakai `json` tipe yang di-sqlite disimpan `text`.

## Switch Guide

### Dari SQLite ke MySQL

1. Buat DB & user:
   ```sql
   CREATE DATABASE smkalfatih CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'smk_user'@'%' IDENTIFIED BY 'strongpass';
   GRANT ALL ON smkalfatih.* TO 'smk_user'@'%';
   ```
2. Update `.env`:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=smkalfatih
   DB_USERNAME=smk_user
   DB_PASSWORD=strongpass
   ```
3. Migrasi: `php artisan migrate --force`
4. Seed production bootstrap (jangan `db:seed` penuh yang berisi dummy fake data):
   ```bash
   php artisan db:seed --class=ProgramSeeder
   php artisan db:seed --class=PageSeeder
   # atau php artisan tinker: User::create superadmin via CLI
   ```
5. Jika migrasi dari sqlite existing, dump & import:
   ```bash
   sqlite3 database/database.sqlite .dump > /tmp/dump.sql
   # edit untuk mysql (hapus PRAGMA) atau pakai tool sqlite2mysql
   mysql smkalfatih < /tmp/dump.sql
   ```

### Tetap SQLite di Production

- Pastikan `database/database.sqlite` writable (`chmod 664`, `chown www-data`).
- Backup copy file, bukan WAL-only.
- Jangan commit `database.sqlite` ke git (`/` already gitignore).

## Production Seeding

- **JANGAN** `php artisan db:seed` penuh di prod (akan buat fake PPDB 20+ dummy).
- Gunakan seeder minimal: `ProgramSeeder`, `PageSeeder` saja, atau buat superadmin via:
  ```bash
  php artisan tinker
  >>> App\Models\User::create(['name'=>'Admin','email'=>'admin@smkalfatih.sch.id','password'=>'...','is_admin'=>true,'is_superadmin'=>true,'is_active'=>true])
  ```
- Sediakan command `php artisan make:superadmin` jika ingin (belum ada, buat manual via tinker atau seeder one-time).

## Index Review (actual usage)

- `ppdb_registrations.status` indexed + `program_id` + composite `status,program_id` untuk dashboard filter.
- `ppdb_registrations.registration_number` unique indexed.
- `pages.slug` unique, `news.slug` unique, `programs.slug` unique.
- `contact_messages.is_read` indexed untuk inbox badge.

Jangan over-index; tambah hanya jika query lambat terbukti.

## Health & Verification

```bash
php artisan migrate:status # all ran?
php artisan db:show # sqlite size / mysql connection
php artisan tinker --execute="echo App\Models\PPDBRegistration::count();"
```

## Queue/Cache/Session (database driver)

- Menggunakan tabel `jobs`, `cache`, `sessions` (sudah migrasi). Tidak perlu Redis di kecil.
- Jika MySQL, pastikan `CACHE_STORE=database` tetap work; `queue:work` butuh cron/supervisor.

## Restore Test

Ikuti `BACKUP_RESTORE.md` untuk restore ke temp DB sebelum menyentuh prod.
