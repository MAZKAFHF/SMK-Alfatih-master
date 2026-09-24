# BACKUP & RESTORE — SMK Tahfizh Al-Fatih

## Strategy

- **Database**: `sqlite` file (`database/database.sqlite`) atau MySQL dump. Backup daily, retain 7 daily + 4 weekly.
- **Media**: `storage/app/public/` (uploads programs/news/gallery/pages/settings). Backup bersama database (tar.gz).

## Backup Script (example cron)

```bash
#!/bin/bash
set -e
DATE=$(date +%Y%m%d_%H%M%S)
APP_DIR=/path/SMK-Alfatih-master
BACKUP_DIR=/backups/smkalfatih
mkdir -p $BACKUP_DIR

# DB
if grep -q "DB_CONNECTION=sqlite" $APP_DIR/.env; then
  cp $APP_DIR/database/database.sqlite $BACKUP_DIR/db-$DATE.sqlite
else
  mysqldump -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_DATABASE | gzip > $BACKUP_DIR/db-$DATE.sql.gz
fi

# Media
tar -czf $BACKUP_DIR/media-$DATE.tar.gz -C $APP_DIR storage/app/public

# Keep only 7 daily
ls -t $BACKUP_DIR/db-* | tail -n +8 | xargs -r rm
ls -t $BACKUP_DIR/media-* | tail -n +8 | xargs -r rm

echo "Backup $DATE done"
```

Add cron `0 2 * * * /path/backup.sh >> /var/log/smk-backup.log 2>&1`

## Manual Backup ( artisan tinker )

- SQLite: copy `database/database.sqlite` ke lokasi aman.
- MySQL: `php artisan db:show` lalu dump.

## Restore Procedure (TEST ON STAGING FIRST, NEVER PROD DIRECTLY)

### SQLite Restore

```bash
php artisan down
cp /backups/db-20260923_020000.sqlite database/database.sqlite
tar -xzf /backups/media-20260923_020000.tar.gz -C .
php artisan migrate:status # verify
php artisan up
```

### MySQL Restore

```bash
php artisan down
# Compressed dump (.sql.gz) must be decompressed via gunzip pipe:
gunzip -c /backups/db-20260923_020000.sql.gz | mysql -h $DB_HOST -u $DB_USER -p"$DB_PASS" $DB_DATABASE
# Alternative: mysql --binary-mode
tar -xzf /backups/media-20260923_020000.tar.gz -C .
php artisan storage:link # if needed
php artisan cache:clear
php artisan up
```

### Backup via Artisan (recommended, env-safe)

```bash
# Uses Laravel config, no password in process args, handles sqlite/mysql automatically
php artisan app:backup --retention=7
# Cron (env not needed, artisan loads .env safely):
0 2 * * * cd /path/SMK-Alfatih-master && php artisan app:backup >> /var/log/smk-backup.log 2>&1
```

### Partial Restore (single table, e.g., ppdb_registrations)

- Restore ke DB temp: `sqlite3 /tmp/restore.sqlite ".restore /backups/db-xxx.sqlite"` lalu `.dump ppdb_registrations` atau MySQL `mysqldump --where`.
- Import via `php artisan tinker` dengan `DB::table()->insert`.

## Restore Test

- Cadangkan fresh install ke `/tmp/smk-restore-test`.
- `cp .env.production.example .env` isi dummy, `touch database/database.sqlite`, `php artisan migrate`, lalu restore satu backup dan verifikasi:

```bash
php artisan tinker --execute="echo App\Models\PPDBRegistration::count();"
php artisan test # sanity
```

Jangan test restore ke DB production.

## Retention & Security

- Enkripsi backup jika berisi PII (PPDB) → `gpg` atau bucket encrypted.
- Jangan simpan backup di `public/`.
- Audit siapa akses backup.

## Verification Checklist

- [ ] Backup file ada & size >0
- [ ] `tar -tzf media-*.tar.gz | head` menampilkan `programs/`, `news/`, etc
- [ ] Restore test di `/tmp` berhasil, app bisa boot `php artisan about`
