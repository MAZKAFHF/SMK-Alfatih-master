# SECURITY — SMK Tahfizh Al-Fatih

## Authentication

- Laravel session guard `web`, driver `database`, lifetime 120m, `httpOnly` true, `sameSite lax`.
- Password hashed `bcrypt` 12 rounds (`password` cast `hashed`).
- `SESSION_SECURE_COOKIE=true` di production (HTTPS).
- Login rate limit: 5 attempts / minute per `email|IP` via `RateLimiter` di `AuthController`. Successful login clears limiter. Failed increments 60s.
- Inactive user (`users.is_active=false`) ditolak login meskipun password benar; middleware `admin` & `superadmin` juga menolak `is_active=false` (403).
- Demo auto-login (`admin@` one-click) hanya aktif jika `APP_ENV != production` **dan** `DEMO_*` terisi.

## Authorization

- Middleware `admin` (is_admin) untuk semua `/admin/*`.
- `superadmin` (is_superadmin) untuk users, login-logs, audit-logs, forceDelete, mass delete.
- Controller juga cek `is_active` dan `last superadmin` protection (tidak bisa nonaktifkan/hapus superadmin terakhir).
- Server-side check di setiap controller, bukan hanya hide button.

## Input Validation

- Semua public & admin forms via `FormRequest` (maxLength, enum, unique, date, exists, file mime).
- Email lower-cased, name/m = trimmed, phone trimmed.
- File upload: `image` rule + `mimes:jpg,jpeg,png,webp` + `max:4096/6144`, `getMimeType()` check `image/*`, tolak `svg` (executable), store dengan random 28 chars via `MediaService`.
- HTML content (Page, News, Announcement, Program description) disanitasi via `Mews\Purifier` (`HTMLPurifier`) dengan `HTML.Allowed` strict (h2,h3,p,b,strong,em,ul,ol,a,blockquote,table,img src/alt, etc). `script, iframe, on* handler, javascript:` diblok. `autoFinalize` true.

## Rate Limiting

- `throttle:5,1` untuk `POST /kontak` dan `POST /ppdb`.
- Custom `RateLimiter` untuk `POST /admin/login` (5/min per email+IP, lockout 60s, 429).
- `GET /ppdb/status` rate limit 10/min per IP.

## CSRF & Session

- `@csrf` di semua form, meta `csrf-token`.
- Session regenerate on login, invalidate + regenerateToken on logout (fixation protection).
- `trustProxies` configurable via `TRUSTED_PROXIES` env (`*` default dev, null atau list IP di prod).

## Security Headers (middleware SecurityHeaders)

- `X-Content-Type-Options: nosniff`
- `Referrer-Policy: strict-origin-when-cross-origin`
- `X-Frame-Options: SAMEORIGIN`
- `Permissions-Policy: camera=(), microphone=(), geolocation=()`
- `Content-Security-Policy` (default-src self, script/style self unsafe-inline + Bunny Fonts, img self data https, etc) — compatible dengan Vite inline.
- `Strict-Transport-Security: max-age=31536000; includeSubDomains` hanya jika HTTPS/prod.

## Data Safety

- SoftDeletes untuk `programs, news, pages, galleries, announcements, contact_messages, ppdb_registrations`. `DELETE` → trash, `restore`, `forceDelete` hanya superadmin.
- Mass delete PPDB (`DELETE /admin/registrations`) hanya superadmin + password re-auth + `confirmation = "HAPUS SEMUA"` + audit log + soft delete (tidak hard).
- AuditLog immutable (`audit_logs`) untuk semua mutasi: `ppdb_status_update, program_create, news_*, page_*, gallery_*, announcement_*, contact_*, user_*, settings_*`. Simpan old/new json, user_id, ip, ua.

## PPDB Privacy

- Status check memerlukan `registration_number` + `birth_date` (rate limited). Sequential number tetap readable tapi butuh second factor untuk mencegah enumeration.
- Tidak menampilkan PII di list (list hanya name/program/status, bukan alamat lengkap).
- Export CSV hanya untuk admin, filtered, tanpa password.

## Storage

- `MediaService` random filename, tidak expose local path, delete old file only if not used elsewhere.
- `php artisan storage:link` required; health check verifies `storage writable`.

## Known Hardening TODO (if needed)

- Add 2FA for superadmin (optional).
- Add CAPTCHA for public contact/ppdb if spam meningkat.
- Rotate APP_KEY periodically.

## Reporting

Laporkan temuan keamanan ke admin superadmin via audit log review. Jangan log password/session secret.
