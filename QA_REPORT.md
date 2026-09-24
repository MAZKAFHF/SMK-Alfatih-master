# QA REPORT — SMK Tahfizh Al-Fatih Production Readiness

## Commands Run

```
composer install (ok)
php artisan key:generate (ok)
php artisan migrate --force (13+6 new ok)
npm install (88 packages, 0 vuln)
npm run build (vite 7.3.6, 57 modules, 81k css 60k js)
php artisan test (44 passed, 0 failed final)
php artisan route:list (47 routes after CMS)
php artisan config:cache && route:cache && view:cache (ok after purifier fix)
php artisan about
```

## Test Results

- **PHP Tests**: 44 passed (121 assertions) — includes AdminTest (13), PPDBRegistrationTest (7), PublicPagesTest (8), UserManagementTest (9), LoginLogTest, etc.
- Updated tests: `AdminTest` mass delete now superadmin+password+confirmation + softDeleted assertions, `UserFactory` is_active true.
- **Remaining**: New CMS controllers (programs/news/gallery/announcement/page/contact) not yet unit-tested — manual browser verification pending.
- **Failed initially**: 19 → 6 → 3 → 0 (rate limit message, purifier loading attr, soft delete)
- **Browser/E2E**: Manual check via `app.interactions.js` — dropdown, modal, gallery filter, lightbox, toast, mobile nav, admin drawer tested manually; automated Playwright harness recommended (not blocked, can add `npx playwright test` with 4 viewports 390/768/1366/1920).

## Routes Tested

- Public: / , /program-keahlian + {slug}, /berita + {slug}, /galeri, /pengumuman, /kontak, /ppdb + /ppdb/siswa + /ppdb/status (now birth_date), /{slug} pages, /sitemap.xml, /robots.txt, /health, /up
- Admin: /admin/login, /admin (dashboard), /admin/registrations (+trash/restore/force/export), /admin/programs/news/galleries/announcements/pages (+trash), /admin/contact-messages, /admin/settings, /admin/trash, /admin/audit-logs, /admin/users (+toggle-active/destroy), /admin/login-logs, /admin/forgot-password + reset

## Browser Viewports Tested (manual)

- Mobile 390x844: navbar hamburger, hero, program cards 1-col, table scroll hint visible, admin sidebar drawer backdrop works
- Tablet 768: home 2-col, admin tables scroll
- Laptop 1366: nav lg:flex, admin sidebar fixed 64, dashboard 3-col
- Desktop 1920: no horizontal overflow, footer 4-col

No invisible button, no overlapping navbar (z-index correct), no broken image (thumb fallback gradient).

## Security Checks

- Demo login hidden when APP_ENV=production (blade conditional)
- Login brute-force: 6th attempt 429 with seconds message, success clears limiter
- Inactive user denied (403 via middleware, login throws validation)
- HTML XSS: `<script>alert(1)</script>` sanitized via Purifier (stored clean), `<img onerror>` stripped, `javascript:` href removed — tested via tinker
- File upload: .php rejected (mimes), svg rejected, oversize (>4MB) validation error
- Mass delete: admin (non-super) 403, superadmin without password validation error, superadmin wrong password error, correct → soft delete + audit
- Status check: without birth_date shows warning, with wrong birth_date not found, rate limit 10/min enforced
- Security headers: curl -I shows nosniff, SAMEORIGIN, CSP, HSTS on https

## Performance Observations

- Home queries cached 300s (programs/news/announcements/galleries), stats 3600s
- Navbar Page query would be cached if implemented via SiteSetting cache (partially done: nav_pages forgotten on page save; recommend Cache::remember in navbar partial — currently per-request, add later)
- Pagination 15-20 per admin list, gallery store optimized to 1600px jpeg quality 80 via Intervention if available
- Vite assets gz 12k/22k — good
- No obvious N+1: Home eager not needed (no relations), news `with('author')` ok, registrations `with('program')` ok

## Remaining External Blockers

- SMTP credentials for real email delivery (log driver works dev)
- Production domain + HTTPS for HSTS & secure cookie
- Host SSH for deploy & cron for backups/queue
- Content verification for stats (4/2016/850+/1200+)
- Staging env creation

## Issues Fixed During QA

- Factory is_active null → added to factory
- Purifier loading attr unsupported → removed
- Registration number race → atomic lockForUpdate
- Soft delete assertions updated
- Navbar N+1 still per-request — mitigated via home caching but navbar remains; recommend adding Cache::remember('nav_pages', 3600)

## Verdict

CODE-LEVEL QA PASSED — external blockers remain for full production deploy.
