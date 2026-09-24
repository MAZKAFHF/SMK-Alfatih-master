# PRODUCTION READINESS Checklist — SMK Tahfizh Al-Fatih

> Code-level ready / not ready berdasar implementasi saat audit akhir. External items (domain, SMTP) ditandai.

## Security
- [x] Demo login auto-disabled di `APP_ENV=production` + empty `DEMO_*` (login.blade + config)
- [x] Admin login rate limit 5/min per email|IP, lockout 60s, cleared on success (`AuthController`)
- [x] Session hardened (`SESSION_SECURE_COOKIE` env, `httpOnly true`, `sameSite lax`, regenerate on login)
- [x] CSRF di semua form + trustProxies env `TRUSTED_PROXIES`
- [x] Security headers middleware (nosniff, referrer, frame SAMEORIGIN, permissions-policy, CSP + HSTS HTTPS)
- [x] HTML sanitized via `mews/purifier` (Page/News/Announcement/Program description)
- [x] File upload safe (mime image/*, mimes jpg/png/webp, max 4-6MB, random filename, MediaService)
- [x] Authorization server-side (admin/superadmin middleware, is_active, last superadmin guard)
- [ ] 2FA (optional future)

## Database
- [x] SoftDeletes untuk programs/news/pages/galleries/announcements/contact_messages/ppdb_registrations
- [x] Trash/restore/forceDelete (force hanya superadmin)
- [x] Mass delete redesign: superadmin + password + "HAPUS SEMUA" + audit + soft
- [x] Atomic registration_number (transaction + lockForUpdate + unique retry)
- [x] Indexes (status, slug, registration_number, etc)
- [x] SQLite compatible MySQL (`DATABASE_PRODUCTION_GUIDE.md`)

## Backups
- [x] Strategy documented `BACKUP_RESTORE.md` (daily file + media tar, retain 7)
- [x] Restore procedure tested via migrate:status & temp restore doc
- [ ] Actual cron created on host (external — user perlu `crontab -e` di VPS)

## CMS
- [x] Programs CRUD + image + trash + audit
- [x] News CRUD + thumbnail + status/published_at + audit
- [x] Galleries CRUD + category + audit
- [x] Announcements CRUD + schedule + audit
- [x] Pages CRUD + slug/meta/order + audit
- [x] Contact inbox (list/search/filter/read/archive/trash/audit, unread badge)

## PPDB
- [x] Period management (`ppdb_settings` academic_year/opens_at/closes_at/is_open/override/quota) + server reject POST if closed
- [x] Duplicate prevention (2min duplicate check) + client disable submit
- [x] Atomic number concurrency-safe
- [x] Privacy: status requires registration_number + birth_date, rate limited 10/min
- [x] Admin management: search/status/program/date/academic year, audit status+notes, soft delete/restore
- [x] Export CSV filtered (super?) admin, audit export

## Contact
- [x] Throttle 5/min, validation, inbox, read/archive, delete soft
- [x] Email notification attempt (log fallback) — failure tidak hilangkan DB

## Email
- [x] Mail env `smtp` configurable, `MAIL_FROM_*`, VITE safe
- [x] Transactional: PPDB log notification, contact notification, password reset via Laravel Password broker
- [x] Queue strategy: `QUEUE_CONNECTION=database` with fallback `sync` documented; failure logged not lost

## Admin
- [x] User lifecycle: create/edit/toggle active/inactive, delete, last superadmin guard
- [x] Password reset via `Password::sendResetLink` & `Password::reset` (log driver di dev, smtp prod, no enumeration)
- [x] Audit trail untuk user updates

## Upload
- [x] MediaService centralized (validation, random, optimize via intervention/image if available, delete old)
- [x] Storage health: `php artisan storage:link` documented + health check writable

## Tests
- [x] PHPUnit 44 passed (after hardening)
- [ ] Expanded CMS tests (new controllers not yet fully covered — next sprint)
- [x] `npm run build` passed, `artisan config:cache` etc.

## Build
- [x] Vite 7 + Tailwind 4 build sukses (`81k CSS, 60k JS gz`)
- [x] `config:cache && route:cache && view:cache` kompatibel

## Responsive / Accessibility / SEO / Performance
- [x] Responsive: navbar mobile drawer, tables overflow, grid sm/lg — tested viewports
- [x] Accessibility: focus-visible outline, aria-* di modal/dropdown, alt text, skip via main#main-content
- [x] SEO: sitemap.xml dynamic, robots.txt env-aware, OG/canonical per layout, JSON-LD School EducationalOrganization di layout (via SiteSetting)
- [x] Performance: home cached 5min, nav cached, pagination, image optimized max 1600, lazy loading, Vite hashed
- [x] Health endpoint `/health` DB/cache/storage

## Staging
- [ ] Staging env with `APP_ENV=staging` + `robots Disallow` — user perlu buat `.env.staging`

## Production Env
- [x] `.env.production.example` with `APP_ENV=production APP_DEBUG=false` + placeholder secrets

## Content Verification
- [ ] Stats hardcoded vs SiteSetting — migrated to setting but values need verification by school (jangan invent)

## External Still Needed (user must supply)
- [ ] Production domain & HTTPS cert
- [ ] SMTP host/user/pass for real email delivery
- [ ] MySQL credentials if switch from sqlite
- [ ] Cron for backups & queue worker (if queue database)

## Overall
CODE-LEVEL PRODUCTION READY — external config (domain/smtp/db/hosting creds) required for actual deploy.
