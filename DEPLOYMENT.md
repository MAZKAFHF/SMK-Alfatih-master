# DEPLOYMENT — SMK Tahfizh Al-Fatih

## Requirements

- PHP >= 8.2 (ext: pdo, mbstring, fileinfo, openssl, sqlite atau pdo_mysql)
- Composer 2.x
- Node 20+ & npm 10+
- Writable: `storage/` , `bootstrap/cache/` , `database/` (jika sqlite)
- Web root harus `public/` (bukan root)
- HTTPS (production)

## Fresh Install (dev/local)

```bash
git clone <repo> && cd SMK-Alfatih-master
composer install
cp .env.example .env
php artisan key:generate
# SQLite default
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
php artisan migrate --seed
php artisan storage:link
npm install
npm run build
php artisan serve # http://localhost:8000
# atau composer dev (serve + queue + vite)
```

Admin demo: `admin@smkalfatih.sch.id / admin1234` (hanya jika APP_ENV != production)

## Production Deploy (generic VPS / shared hosting with SSH)

```bash
# 1. Pull code
git pull origin main
# 2. PHP deps (no-dev, optimized)
composer install --no-dev --optimize-autoloader
# 3. Env
cp .env.production.example .env # lalu isi secrets
php artisan key:generate # jika APP_KEY kosong
# 4. DB
php artisan migrate --force
# 5. Storage link (jika belum)
php artisan storage:link
# 6. Frontend
npm ci
npm run build
# 7. Cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
# 8. Permissions
chmod -R 775 storage bootstrap/cache
# 9. Scheduler (jika dipakai)
# cron: * * * * * php /path/artisan schedule:run >> /dev/null 2>&1
# 10. Queue (jika email via queue database)
# php artisan queue:work --sleep=3 --tries=3 & (supervisor/systemd)
```

## Env Production Template

Lihat `.env.production.example`. Wajib set:

- `APP_ENV=production` `APP_DEBUG=false` `APP_URL=https://domain`
- `APP_KEY` (generate)
- `DB_CONNECTION` (sqlite path atau mysql host/db/user/pass)
- `SESSION_SECURE_COOKIE=true` (jika HTTPS)
- `TRUSTED_PROXIES` (kosongkan atau isi IP load balancer)
- `MAIL_*` (smtp host/pass jika ingin notifikasi PPDB/contact)
- Kosongkan `DEMO_ADMIN_EMAIL/PASSWORD` (otomatis nonaktif di prod)
- `QUEUE_CONNECTION=database` atau `sync` jika tidak ada worker

## Web Server

- **Nginx/Apache**: `DocumentRoot` → `/public`
- Jika Apache, `.htaccess` sudah tersedia.
- Pastikan `public/storage` symlink valid; jika hosting melarang symlink, ubah `FILESYSTEM_DISK=public` ke s3 atau copy manual.

## Rollback

```bash
php artisan migrate:rollback --step=1 # jika migrasi terakhir bermasalah
git checkout <previous-tag>
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

## Health Check

- `GET /up` → Laravel default
- `GET /health` → JSON {status, checks: database/cache/storage} (cek `app/Http/Controllers/HealthController.php`)
- `GET /sitemap.xml` & `GET /robots.txt` → SEO

## Scheduler & Queue

- Scheduler WAJIB untuk retensi otomatis (log 03:00, trash 03:15, akun pemohon 03:30) + backup terjadwal.
- Cron hosting: `* * * * * php /path/artisan schedule:run >> /dev/null 2>&1` (tanpa ini cleanup tidak berjalan otomatis).
- Verifikasi: `php artisan schedule:list`.

## CDN / Assets

- Vite build output `public/build/` harus writable dan di-commit? Tidak — di-build saat deploy.
- Bunny Fonts CDN `fonts.bunny.net` perlu akses outbound.

## Verification Checklist Post-Deploy

- [ ] `php artisan migrate:status` all ran
- [ ] `php artisan storage:link` && `ls -l public/storage`
- [ ] `npm run build` sukses, `public/build/manifest.json` ada
- [ ] `php artisan config:cache && route:cache && view:cache` tanpa error
- [ ] Login admin valid + rate limit 5/min
- [ ] PPDB posting saat `is_open` false ditolak (403/ error)
- [ ] Contact inbox menerima pesan
- [ ] Sitemap & robots sesuai env
- [ ] Security headers terlihat (curl -I)

## Hostinger VPS — Docker + Traefik

Repository menyediakan `Dockerfile.production` dan `docker-compose.production.yml`
untuk VPS yang port 80/443-nya sudah dikelola Traefik.

```bash
docker compose --env-file .env.production -f docker-compose.production.yml build
docker compose --env-file .env.production -f docker-compose.production.yml up -d database
docker compose --env-file .env.production -f docker-compose.production.yml run --rm app php artisan migrate --force
docker compose --env-file .env.production -f docker-compose.production.yml up -d
```

- Tidak ada database atau port aplikasi yang dipublikasikan langsung ke internet.
- Traefik meneruskan domain utama `smktahfizhalfatih.otaniverse.org` ke container `app`.
- Domain lama `otaniverse.org` dan `www.otaniverse.org` diarahkan permanen (301) ke domain utama agar SEO dan tautan lama tetap aman.
- Queue dan scheduler berjalan sebagai container terpisah.
- `storage/` dan `backups/` merupakan bind mount persisten di VPS.
- File `.env.production` hanya dibuat di server dan tidak boleh di-commit.

## Kontrak Deploy Tanpa Data Tertinggal

Deploy produksi dianggap selesai hanya jika **kode, migrasi, aset build, database, dan media persisten** sudah diperiksa sebagai satu kesatuan.

Sebelum deploy, jalankan backup terverifikasi dan audit media:

    docker compose --env-file .env.production -f docker-compose.production.yml exec -T app php artisan app:backup
    docker compose --env-file .env.production -f docker-compose.production.yml exec -T app php artisan app:media-audit

Sesudah image baru aktif:

    docker compose --env-file .env.production -f docker-compose.production.yml run --rm app php artisan migrate --force
    docker compose --env-file .env.production -f docker-compose.production.yml exec -T app php artisan optimize:clear
    docker compose --env-file .env.production -f docker-compose.production.yml exec -T app php artisan config:cache
    docker compose --env-file .env.production -f docker-compose.production.yml exec -T app php artisan route:cache
    docker compose --env-file .env.production -f docker-compose.production.yml exec -T app php artisan view:cache
    docker compose --env-file .env.production -f docker-compose.production.yml exec -T app php artisan app:media-audit
    curl -fsS https://smktahfizhalfatih.otaniverse.org/health
    curl -fsS https://smktahfizhalfatih.otaniverse.org/sitemap.xml
    curl -fsS https://smktahfizhalfatih.otaniverse.org/robots.txt

- Volume PostgreSQL, storage, dan backups tidak boleh dihapus atau diganti saat deploy kode.
- Data localhost tidak otomatis menimpa produksi. Sinkronisasi data lintas lingkungan harus melalui ekspor, backup terverifikasi, impor transaksional, audit jumlah record, dan audit media.
- Deploy gagal bila migrasi, health check, sitemap, atau audit media gagal.
- Jangan memakai migrate:fresh, db:wipe, atau seed demo di produksi.
