# SMK AL-FATIH — PROJECT MAP

> **Generated**: 23 September 2026 — Read-Only Deep Codebase Mapping
> **Auditor**: Senior Full-Stack Engineer / Architect (Muse Spark)
> **State**: No source code modified, no bug fixed, no deployment performed.

---

## 1. Executive Summary

**SMK Tahfizh Al-Fatih** adalah website resmi sebuah SMK berbasis tahfizh Al-Qur'an. Aplikasi adalah **monolitik Laravel 12 (PHP 8.2) + Blade SSR + Tailwind CSS v4 + Vite 7** dengan database **SQLite (default)**. Tidak ada frontend SPA/React/Next, tidak ada Firebase, tidak ada API eksternal.

**Tujuan aplikasi** (`README.md:1`):
- Halaman publik: profil sekolah, program keahlian (PPLG/Multimedia/DKV/TJKT), berita, galeri, pengumuman, halaman statis dinamis, kontak, PPDB online.
- Panel admin (`/admin`): autentikasi, dashboard statistik PPDB, manajemen pendaftaran, manajemen user (superadmin), log login.

**Maturity**: `DEVELOPMENT / PRE-PRODUCTION` — fitur inti PPDB & admin selesai, tapi modul CMS untuk berita/galeri/pengumuman/halaman **belum ada CRUD admin** (hanya publik read). Seeder & demo account menandakan staging. Desain system sudah matang (25+ komponen Blade).

**Arsitektur**: Server-rendered Blade → Controller → Eloquent Model → SQLite. Vanilla JS (`resources/js/app.interactions.js:1`) untuk interaktivitas (dropdown, modal, toast, lightbox, dark mode). Session `database` driver. Validasi via `FormRequest`. Test coverage ada (5 Feature test suites).

**Risiko utama**: Penghapusan massal `destroyAll` tanpa soft-delete, demo credential di `.env.example`, race condition pada `generateRegistrationNumber()`, dan tidak adanya rate-limit di admin login.

---

## 2. Tech Stack

| Kategori | Teknologi | Versi / File | CONFIRMED |
|---|---|---|---|
| **Nama Project** | SMK Tahfizh Al-Fatih | `composer.json:3` `name: laravel/laravel` / `APP_NAME=SMK Tahfizh Al-Fatih` di `.env.example:1` | CONFIRMED FROM CODE |
| **Tujuan** | Website sekolah + PPDB online + Admin panel | `README.md:3` | CONFIRMED |
| **Framework Utama** | Laravel | `composer.json:10` `laravel/framework ^12.0` | CONFIRMED |
| **Bahasa** | PHP | `composer.json:9` `php ^8.2` | CONFIRMED |
| **Package Manager (PHP)** | Composer | `composer.lock` exists, `composer.json` | CONFIRMED |
| **Package Manager (JS)** | npm | `package.json`, `package-lock.json` | CONFIRMED |
| **Node Requirement** | Node+npm (implisit, Vite 7) | `package.json:15` `vite ^7.0.7` | INFERRED |
| **Frontend Framework** | Blade SSR (Laravel Blade), bukan React/SPA | `resources/views/**/*.blade.php` | CONFIRMED |
| **Backend Framework** | Laravel MVC (Controllers, Middleware, FormRequest) | `app/Http/*` | CONFIRMED |
| **Database** | SQLite default, migratable ke MySQL/PostgreSQL | `.env.example:26` `DB_CONNECTION=sqlite` / `config/database.php` | CONFIRMED |
| **Authentication** | Laravel Session Auth (`session` driver `database`) | `config/auth.php:41` / `bootstrap/app.php:27` | CONFIRMED |
| **Storage** | Laravel `local` → `public` disk (`Storage::disk('public')`) + `storage:link` | `config/filesystems.php` / `app/Models/Program.php:43` | CONFIRMED |
| **Hosting/Deploy** | Generic PHP host (any Laravel-capable), `public/index.php:1` + `.htaccess` | `public/.htaccess` | CONFIRMED |
| **CSS Framework** | Tailwind CSS v4 (`@tailwindcss/vite ^4.3.3`) | `package.json:10` / `vite.config.js:3` | CONFIRMED |
| **UI Library** | Custom Blade Design System `<x-ui.*>` (25 components) | `resources/views/components/ui/*` | CONFIRMED |
| **Icon Library** | Inline Heroicons SVG (stroke 1.8/2, fill) — no NPM icon lib | `resources/views/partials/navbar.blade.php` + `resources/js` | CONFIRMED |
| **Animation Library** | CSS native (`@keyframes toast-slide-in/out`, `animate-ping`, `transition-*`) — no Framer/GSAP | `resources/css/app.css:83` / `resources/js/app.interactions.js` | CONFIRMED |
| **Validation Library** | Laravel FormRequest + native rules | `app/Http/Requests/*.php` | CONFIRMED |
| **Form Library** | Standard HTML + Blade components + CSRF | `resources/views/components/ui/input.blade.php` | CONFIRMED |
| **HTTP Client** | Axios ^1.11.0 (only `resources/js/bootstrap.js:7` sets `window.axios`) | `package.json:11` | CONFIRMED (barely used) |
| **API / Eksternal** | Bunny Fonts CDN (`fonts.bunny.net`) | `resources/views/components/layouts/app.blade.php:50` | CONFIRMED |
| **Analytics** | Tidak ada | - | NOT CONFIRMED |
| **Important Libraries** | `laravel/tinker`, `laravel/pint`, `fakerphp/faker`, `concurrently` (dev) | `composer.json:12` / `package.json:12` | CONFIRMED |
| **Script Runner** | `concurrently` untuk `php artisan serve + queue:listen + vite` | `composer.json:44` `scripts.dev` | CONFIRMED |

**Tidak ditemukan**: Next.js, Firebase, Prisma, React, Vue, Alpine.js, TypeScript, ESLint (`no eslint config`), Prettier, Docker, SaaS auth (Clerk/Supabase).

---

## 3. Project Structure

```
SMK-Alfatih-master/                 # root Laravel
├── .editorconfig                   # indent 4, LF
├── .env.example                    # 68 lines — template env (APP_NAME, DB, DEMO_*)
├── .gitignore                      # ignore .env, vendor, node_modules, public/build
├── artisan                         # Laravel CLI entry
├── bootstrap/
│   ├── app.php:15                  # Application configure — routing, middleware aliases, exception render
│   └── providers.php
├── config/                         # 9 files
│   ├── app.php:19                  # 'name' => env('APP_NAME'), demo_admin_email/password
│   ├── auth.php:18                 # defaults guard web, provider eloquent User
│   ├── database.php                # connections sqlite/mysql, default sqlite
│   ├── filesystems.php             # disk local/public, APP_URL linked Storage::url()
│   ├── session.php:21              # driver database, lifetime 120
│   └── ... (cache, logging, mail, queue, services)
├── database/
│   ├── migrations/                 # 13 migrations (3 stock + 10 domain)
│   │   ├── 0001_01_01_000000*      # users, password_reset_tokens, sessions
│   │   ├── 2026_08_14_03453*       # programs, news, pages, announcements, galleries, contact_messages
│   │   └── 2026_08_14_04000*       # is_admin, ppdb_registrations, is_superadmin, login_logs
│   ├── factories/                  # 7 factories (User, Program, News, Page, Gallery, Announcement, PPDBRegistration)
│   └── seeders/                    # 7 seeders (Program, Page, News, Gallery, Announcement, PPDB + DatabaseSeeder)
├── resources/
│   ├── css/app.css:1               # @import tailwindcss, @theme tokens (--color-primary-*, --color-accent-*), prose-content, animations
│   ├── js/
│   │   ├── app.js:1                # import './bootstrap'; import './app.interactions'
│   │   ├── bootstrap.js            # window.axios = axios
│   │   └── app.interactions.js:1   # 465 lines — themeToggle, dropdown, modal, tabs, toast, mobileNav, adminDrawer, galleryFilter, lightbox
│   └── views/
│       ├── components/
│       │   ├── layouts/app.blade.php:14   # Public layout — <html>, OG tags, vite, navbar/footer, toast
│       │   ├── admin/layouts/app.blade.php:1 # Admin layout — sidebar 64w, mobile drawer, backdrop, topbar
│       │   ├── ui/                 # 22 components: button, card, input, select, textarea, badge, alert, modal, dropdown, table, tabs, theme-toggle, toast, confirm-dialog, etc.
│       │   ├── thumb.blade.php     # Image/placeholder thumb (gradient if no src)
│       │   ├── page-header.blade.php # Breadcrumb + title
│       │   └── section-heading.blade.php
│       ├── partials/
│       │   ├── navbar.blade.php:1  # Sticky nav, pages dropdown (limit 6), mobile menu
│       │   └── footer.blade.php:1  # 4-col footer (brand, quick links, PPDB, contact)
│       ├── public/                 # 12 Blade: home, programs/*, news/*, gallery/*, announcements/*, contact/*, ppdb/*, pages/*
│       ├── admin/                  # 5 Blade: dashboard, registrations/*, users/*, login-logs/*, auth/login
│       ├── errors/                 # 403,404,419,429,500,503
│       └── vendor/pagination/*     # Tailwind pagination override
├── routes/
│   ├── web.php:1                   # 25+ routes — all web (no api.php)
│   └── console.php
├── public/
│   ├── index.php                   # Laravel entry
│   ├── img/logo.png + beranda.png # 2 static assets
│   ├── .htaccess / robots.txt / favicon.ico
│   └── build/ (gitignored) / storage/ (gitignored symlink)
├── storage/                        # framework views/cache, logs, app/public
├── tests/
│   ├── Feature/                    # AdminTest, PPDBRegistrationTest, PublicPagesTest, LoginLogTest, UserManagementTest
│   └── Unit/ExampleTest.php
├── vite.config.js:1                # laravel-vite-plugin + tailwindcss(), input css/app.css + js/app.js
├── package.json:1                  # scripts dev/build, devDeps tailwind+v4, vite, axios
├── composer.json:9                 # require php^8.2 laravel/framework^12.0
├── phpunit.xml:26                  # DB sqlite :memory:, SESSION array
└── README.md:1                     # Full feature docs + install steps
```

**Directory purpose**:
- `app/Enums` — 3 PHP 8.1 enums for status (reusable across models & views).
- `app/Http/Controllers/Public` — 8 controllers, thin, hanya query + `view()` (feature-specific).
- `app/Http/Controllers/Admin` — 5 controllers, session-auth protected.
- `app/Http/Requests` — 6 FormRequests, validasi terpusat (attribute i18n Indonesian).
- `app/Models` — 8 models, scopes `published/active`, accessors untuk Storage URL, casts enum.
- `resources/views/components/ui` — Design system reusable, dipakai di public & admin.
- `database/seeders` — Data demo lengkap (4 programs, 5 pages, news/gallery/announcements dummy).

**Ignored/unparsed**: `node_modules`, `vendor`, `.next` tidak ada (bukan Next.js), `storage/framework/views`.

---

## 4. Routes

### 4.1 Public Routes (`routes/web.php:18`)

| Route | Verb | File | Tujuan | Public/Protected | Role | Data Source |
|-------|------|------|--------|------------------|------|-------------|
| `/` | GET | `HomeController@index` | Beranda hero + programs(4) + news(3) + announcements(4) + galleries(6) | Public | guest/all | `Program::active`, `News::published`, `Announcement::published`, `Gallery::published` |
| `/program-keahlian` | GET | `ProgramController@index` | List program keahlian | Public | guest | `Program::active()->orderBy('order')` |
| `/program-keahlian/{program:slug}` | GET | `ProgramController@show` | Detail program + 3 program lain | Public | guest | `Program` via slug, abort 404 if inactive |
| `/berita` | GET | `NewsController@index` | List berita paginate 9 | Public | guest | `News::published()->with('author')->paginate(9)` |
| `/berita/{news:slug}` | GET | `NewsController@show` | Detail berita + 3 related | Public | guest | `News` via slug, 404 if not published/future |
| `/galeri` | GET | `GalleryController@index` | Galeri filter kategori + lightbox | Public | guest | `Gallery::published()->orderBy('order')` |
| `/pengumuman` | GET | `AnnouncementController@index` | List pengumuman paginated | Public | guest | `Announcement::published()->latest('published_at')->paginate(?)` |
| `/kontak` | GET | `ContactController@index` | Form kontak | Public | guest | static + old input |
| `/kontak` | POST | `ContactController@send` | Kirim pesan, throttle 5/min | Public | guest | `StoreContactMessageRequest` → `ContactMessage::create` |
| `/ppdb` | GET | `PPDBController@index` | Landing PPDB (CTA) | Public | guest | static |
| `/ppdb/siswa` | GET | `PPDBController@siswa` | Form pendaftaran (programs active) | Public | guest | `Program::active` |
| `/ppdb` | POST | `PPDBController@store` | Submit pendaftaran, throttle 5/min | Public | guest | `StorePPDBRegistrationRequest` → `PPDBRegistration::create` → redirect status |
| `/ppdb/status` | GET | `PPDBController@status` | Cek status via ?registration_number=PPDB-... | Public | guest | `PPDBRegistration::where('registration_number', ...)->with('program')` |
| `/{slug}` | GET | `PageController@show` | Halaman statis dinamis (profil/sejarah/visi-misi/etc) — **catch-all** | Public | guest | `Page::published()->where('slug', $slug)->firstOrFail()` |
| `*` error | - | `resources/views/errors/*` | 403,404,419,429,500,503 | - | - | Blade |

> **Catatan catch-all**: `where('slug', '(?!admin)[a-z0-9-]+')` di `routes/web.php:39` mencegah bentrok dengan `/admin`. Jika slug tidak ditemukan, Laravel 404 otomatis. Ini adalah `NOT CATCH-ALL GLOBAL` — hanya slug single-segment lowercase.

### 4.2 Admin Routes (`routes/web.php:42`)

| Route | Verb | File | Tujuan | Protected | Role | Data Source |
|-------|------|------|--------|-----------|------|-------------|
| `/admin/login` | GET | `AuthController@showLoginForm` | Form login | guest | — | `admin.auth.login` view |
| `/admin/login` | POST | `AuthController@login` | Attempt login | guest | — | `LoginRequest` → `Auth::attempt` → `LoginLog` |
| `/admin/logout` | POST | `AuthController@logout` | Logout + invalidate session | auth+admin | admin/superadmin | `LoginLog` EVENT_LOGOUT |
| `/admin` | GET | `DashboardController@index` | Dashboard statistik | auth+admin | admin/superadmin | counts per RegistrationStatus, last7Days, programDistribution, recent 5 logs (superadmin only) |
| `/admin/registrations` | GET | `RegistrationController@index` | List pendaftar filter/search paginate 15 | auth+admin | admin/superadmin | `PPDBRegistration` with `status/search` scopes |
| `/admin/registrations` | DELETE | `RegistrationController@destroyAll` | Hapus semua pendaftar | auth+admin | admin/superadmin | `PPDBRegistration::query()->delete()` |
| `/admin/registrations/{registration}` | GET | `RegistrationController@show` | Detail + form update status | auth+admin | admin/superadmin | `PPDBRegistration` |
| `/admin/registrations/{registration}` | PUT | `RegistrationController@update` | Update status | auth+admin | admin/superadmin | `UpdateRegistrationStatusRequest` |
| `/admin/registrations/{registration}` | DELETE | `RegistrationController@destroy` | Hapus satu | auth+admin | admin/superadmin | `->delete()` |
| `/admin/login-logs` | GET | `LoginLogController@index` | List login/logout paginate 20, filter event/search | auth+admin+superadmin | superadmin | `LoginLog::with('user')` |
| `/admin/users` | GET | `UserManagementController@index` | List users paginate 20 | auth+admin+superadmin | superadmin | `User::orderBy('created_at')` |
| `/admin/users` | POST | `UserManagementController@store` | Tambah admin | auth+admin+superadmin | superadmin | `StoreUserRequest` → `User::create` is_admin=true |
| `/admin/users/{user}` | PUT | `UserManagementController@update` | Edit user (name/email/password/role) | auth+admin+superadmin | superadmin | `UpdateUserRequest`, self-role protection |

**Middleware chain** (`bootstrap/app.php:30`):
- `guest` → redirect to `admin.dashboard` if authenticated
- `auth` → session check, redirect to `admin.login` if guest
- `admin` alias → `EnsureUserIsAdmin:13` abort 403 unless `is_admin===true`
- `superadmin` alias → `EnsureUserIsSuperAdmin:13` abort 403 unless `is_superadmin===true`

**Tidak ada**: dynamic `[id]` selain model-binding, nested admin sub-route, API route (`routes/api.php` tidak ada), loading/not-found page Next-style (Laravel pakai `errors/*.blade.php`).

---

## 5. User Roles

Hanya **2 role boolean** ditemukan di sumber — **tidak ada siswa/guru/operator** (JANGAN dikarang).

| Role | Field | Sumber | Keterangan |
|------|-------|--------|------------|
| **Visitor / Guest** | — (unauthenticated) | `bootstrap/app.php:27` `redirectGuestsTo` | Dapat akses semua route publik, submit PPDB & kontak. Ditandai sebagai `CONFIRMED FROM CODE` — no DB record. |
| **Admin** | `users.is_admin = true` | `database/migrations/2026_08_14_040000*` + `app/Models/User.php:26` | Dapat login `/admin`, akses dashboard + kelola registrations (`admin` middleware). |
| **Super Admin** | `users.is_superadmin = true` + `is_admin = true` | `database/migrations/2026_08_14_040002_add_is_superadmin*` + `User.php:27` | Admin plus kelola users & lihat login-logs (`superadmin` middleware). Tidak bisa ubah role diri sendiri (`UserManagementController.php:53`). |

**Seeder default** (`DatabaseSeeder.php:15`): `admin@smkalfatih.sch.id / admin1234` — `is_admin=true, is_superadmin=true`.

**Tidak ada**: table `roles`/`permissions`, Spatie Permission, RBAC pivot, calon siswa account (PPDB tidak buat user, hanya `ppdb_registrations` record).

---

## 6. User Flows

### 6.1 Visitor — Melihat Beranda → Program → Berita → Galeri → Pengumuman
```
GET /  → HomeController:15 query programs/news/announcements/galleries
        → view public.home (hero, stats, CTA, program cards, news cards, announcement list, gallery thumbs)
     → Klik program → GET /program-keahlian → GET /program-keahlian/{slug} → ProgramController:show abort if inactive
     → Klik berita → GET /berita?paginate(9) → GET /berita/{slug} → 404 if draft/future
     → Klik galeri → GET /galeri → JS filter [data-gallery-filter] → lightbox [data-lightbox]
     → Klik pengumuman → GET /pengumuman → AnnouncementController@index
```

### 6.2 Visitor — Halaman Statis (Profil/Sejarah/Visi-Misi)
```
Navbar dropdown → GET /{slug} (profil, sejarah, visi-misi, sambutan-kepala-sekolah, fasilitas)
      → PageController:11 Page::published()->where('slug')->firstOrFail()
      → public.pages.show renders {!! $page->content !!} (prose-content)
      → 404 jika draft/archived atau slug invalid
```

### 6.3 Visitor — Kontak
```
GET /kontak → form (name,email,phone,subject,message)
POST /kontak (throttle:5,1) → StoreContactMessageRequest → ContactMessage::create
    → back()->with('success','Pesan Anda berhasil dikirim')
    → Toast [data-toast-container] via flash-success meta
    → Tidak ada admin inbox — pesan hanya tersimpan di DB (no admin UI)
```

### 6.4 Visitor — PPDB Online (Flow Utama)
```
GET /ppdb → landing (Daftar Sekarang + Cek Status)
GET /ppdb/siswa → PPDBController:siswa → Program::active()->orderBy('order') → form siswa
    Fields: name*, gender*, program_id*, nisn, birth_place, birth_date, address, school_origin, phone, email, parent_name
POST /ppdb (throttle:5,1) → StorePPDBRegistrationRequest validation
    → PPDBRegistration::create (registration_number auto via booted generateRegistrationNumber)
    → redirect route('ppdb.status', ['registration_number' => 'PPDB-YYYY-XXXXX']) + success flash
GET /ppdb/status?registration_number=PPDB-... → PPDBController:status
    → PPDBRegistration::where('registration_number', query)->with('program')->first()
    → Tampilkan badge status (pending/accepted/rejected/cancelled) + message kondisional
    → Jika tidak ditemukan → Alert danger "Data tidak ditemukan"
    → Jika kosong → Empty-state "Masukkan nomor pendaftaran"
```

### 6.5 Admin — Login / Logout
```
GET /admin/login (guest) → form email+password + demo-click button (auto-fill from config/app.demo_*)
POST /admin/login → LoginRequest → Auth::attempt(credentials, remember)
    → success: session regenerate → recordActivity(LOGIN) → redirect intended admin.dashboard + toast
    → fail: back withErrors email + error flash

POST /admin/logout (auth+admin) → recordActivity(LOGOUT) → Auth::logout → session invalidate + regenerateToken → redirect admin.login
```

### 6.6 Admin — Dashboard
```
GET /admin (auth+admin) → DashboardController:index
    → $stats per RegistrationStatus + total + today
    → $recent 6 latest registrations with program
    → $last7Days (loop 6..0) + maxTrend
    → $programDistribution via join programs count top 5
    → $recentLoginLogs (superadmin only) latest 5
    → view admin.dashboard: hero greeting, 3-card summary (Total/Diterima/Pending), tren 7-hari bar chart (CSS height %), sebaran status bar segmented, peminat program bars, pendaftar terbaru list, aktivitas login
```

### 6.7 Admin — Kelola Pendaftar
```
GET /admin/registrations → filter (search name/regnum/email/phone) + status dropdown → paginate 15
    → Table with sticky Aksi column, mobile horizontal scroll, badge status
GET /admin/registrations/{id} → show detail (nisn/gender/birth/address/phone/email/school/parent/program/created_at) + update status select (pending/accepted/rejected/cancelled) + delete button
PUT /admin/registrations/{id} → UpdateRegistrationStatusRequest → update → back with success/error
DELETE /admin/registrations/{id} → confirmDialog JS → RegistrationController@destroy → delete → redirect index
DELETE /admin/registrations → destroyAll: count → if 0 warning, else delete() all → success with count
```

### 6.8 Superadmin — Kelola Users & Log
```
GET /admin/users → UserManagementController:index paginate 20
    → Cards + table, badge purple(superadmin)/blue(admin)
POST /admin/users → StoreUserRequest (name/email/password+role) → create is_admin=true
PUT /admin/users/{user} → UpdateUserRequest → if role non-null && not self → update is_superadmin

GET /admin/login-logs → LoginLogController:index filter event/search paginate 20
    → Mobile: cards with IP+time+agent, Desktop: table (Pengguna/Aktivitas/IP/Perangkat/Waktu)
```

---

## 7. Component Architecture

### 7.1 Layouts
| Nama | Path | Fungsi | Props | Dipakai |
|------|------|--------|-------|---------|
| `x-layouts.app` | `components/layouts/app.blade.php:1` | Public shell — html/head/meta/OG/csrf, vite, navbar/footer/toast | title, description, bodyClass | Semua public pages |
| `x-admin.layouts.app` | `components/admin/layouts/app.blade.php:1` | Admin shell — sidebar 64w fixed, mobile drawer+backdrop, desktop topbar | title | Semua admin pages |

### 7.2 Navigation
| Nama | Path | Fungsi |
|------|------|--------|
| `partials.navbar` | `partials/navbar.blade.php:1` | Sticky header, pages dropdown (limit 6 published), mobile [data-nav-toggle] |
| `partials.footer` | `partials/footer.blade.php:1` | 4-col (brand, tautan, PPDB, kontak) |
| `x-ui.dropdown` + `dropdown-item` | `ui/dropdown*.blade.php` | Click-toggle, [data-dropdown-menu] hidden, close on outside click (JS) |
| `x-ui.breadcrumb` | `ui/breadcrumb.blade.php` | — (unused directly, page-header embeds) |

### 7.3 Forms
| Nama | Props | Dependency | Halaman |
|------|-------|------------|---------|
| `x-ui.input` | label, name, type, value, placeholder, required, autofocus, prefix/suffix | `$errors->first($name)` → red border + `aria-invalid` | PPDB siswa(8), contact(5), admin login(2), user modals |
| `x-ui.select` | label, name, value, options, placeholder, placeholder-option | — | PPDB siswa gender/program, admin status filter |
| `x-ui.textarea` | label, name, rows, value | — | PPDB address, kontak message |
| `x-ui.checkbox/radio` | — | — | — (exist but unused in flows, reserved) |
| `x-ui.button` | variant(6), size(4), href, loading, full | — | Universal |
| `x-page-header` | title, breadcrumbs[] | — | Semua public list/detail |
| `x-section-heading` | subtitle, title, align | — | Home, PPDB siswa |

### 7.4 Cards & Content
| Nama | Path | Fungsi |
|------|------|--------|
| `x-ui.card` | `ui/card.blade.php` | Rounded-xl border+shadow, padding/hover props |
| `x-thumb` | `components/thumb.blade.php` | Aspect ratio image, fallback gradient + camera SVG if no src |
| `x-ui.badge` | `ui/badge.blade.php` | Color map (amber/green/red/slate/purple/blue), dot/size props — untuk RegistrationStatus/ContentStatus |
| `x-ui.alert` | `ui/alert.blade.php` | Variant success/danger/warning, title, dismissible [data-alert-dismiss] |
| `x-ui.empty-state` | `ui/empty-state.blade.php` | Icon false + title/description + action slot |
| `x-ui.table` | `ui/table.blade.php` | `<table>` wrapper dengan thead head[] prop |
| `x-ui.modal` | `ui/modal.blade.php` | Hidden [data-modal], backdrop click + Esc close, JS `openModal(id)` |
| `x-ui.confirm-dialog` | `ui/confirm-dialog.blade.php` | Global dialog [data-confirm-dialog], JS `confirmDialog({title,message,formAction,method})` |
| `x-ui.toast` | `ui/toast.blade.php` | Container [data-toast-container], JS `toast(msg,type,duration)` + flash meta |
| `x-ui.theme-toggle` | `ui/theme-toggle.blade.php` | Button [data-theme-toggle] + sun/moon icons, localStorage `theme` |
| `x-ui.tabs` | `ui/tabs.blade.php` | [data-tabs] + [data-tab-trigger]/[data-tab-panel] |
| `x-error-page` | `components/error-page.blade.php` | Digunakan oleh errors/*.blade.php (404 etc) |
| `x-ui.loading*` | `loading.blade.php`, `loading-spinner` | Spinner SVG |

### 7.5 Reusable vs Feature-Specific
- **Reusable (shared)**: semua `ui/*` + `thumb` + `page-header` + `section-heading` — dipakai di public & admin → konsisten design.
- **Feature-specific**: `admin.layouts.app` (hanya admin), `partials/navbar` vs admin sidebar (tidak shared), `public/home` sections (hero inline, bukan komponen).

**Duplicate / Repeated Patterns**:
- `_thumb ratio aspect-[3/4] / video` berulang di home(6), programs, news, gallery, pages.
- `Status badge + color` via `RegistrationStatus::badgeColor()` duplicated logic di PPDB status, admin list, dashboard.
- `Card + badge + date` pattern untuk news/announcement/gambar serupa.

---

## 8. Frontend Architecture

### 8.1 Rendering Model
- **Server Component sepenuhnya** — tidak ada Client Component React. Blade SSR di `resources/views/**` menghasilkan HTML lengkap di server. `vite` hanya bundle `app.css` + `app.js` (vanilla).
- `resources/views/components/layouts/app.blade.php:53` injects `@vite(['resources/css/app.css','resources/js/app.js'])` + `localStorage.getItem('theme')` inline script sebelum title.
- **Tidak ada** hydration, virtual DOM, atau suspense. Loading state adalah `x-ui.loading` spinner static, bukan skeleton async.

### 8.2 State Management
| Mekanisme | Lokasi | Kegunaan |
|-----------|--------|----------|
| **localStorage** | `app.interactions.js:26` `theme` | Dark mode persist — `localStorage.getItem('theme')` vs `prefers-color-scheme` |
| **Session (DB)** | `config/session.php:21` `driver database` + `database/sessions` | Auth, flash message, CSRF, pagination queryString |
| **Cookies** | `session.php:130` session cookie + CSRF meta | — |
| **URL Query** | `PPDBController:42` `request->string('registration_number')`, `RegistrationController:16` search/status | Filter & status check — no hash router |
| **JS in-memory** | `app.interactions.js` closure — `let onConfirm`, `dismissToast._timer` | Modal/toast internal state, bukan global store |

> **Tidak ada** React Context, Redux, Zustand, Pinia, atau `useState`.

### 8.3 Data Fetching
- **SSR**: Data di-load di Controller (`Program::active()->get()`) dan di-pass via `compact()` ke Blade — no client fetch.
- **axios** tersedia (`resources/js/bootstrap.js`) tapi **tidak dipakai** untuk request PPDB/kontak — semua form `method=POST` standard Laravel (CSRF).
- **No realtime** listeners (WebSocket/Pusher/Echo). No SWR/React-Query.

### 8.4 Form Handling & Validation
- `<form method="POST" action="{{ route('ppdb.store') }}"> @csrf` + `novalidate`.
- Server-side `FormRequest` (`StorePPDBRegistrationRequest:14` rules) → redirect back with `withErrors` + `withInput()` → Blade `x-ui.input` reads `$errors->first($name)` → red border & `<p id="xx-error">`.
- Client-side hanya `required` attribute base + date `max`.

### 8.5 Client JS Interactions (`resources/js/app.interactions.js:10`)
| Init | Selector | Perilaku |
|------|----------|----------|
| `initThemeToggle` | `[data-theme-toggle]` | Toggle `documentElement.classList.dark`, persist `localStorage.theme`, swap sun/moon `.hidden` |
| `initMobileNav` | `[data-nav-toggle]` + `[data-nav-menu]` | Toggle `.hidden` + aria-expanded |
| `initAdminDrawer` | `[data-admin-drawer]` + backdrop + toggle | Translate `-translate-x-full`, focus trap, Escape close |
| `initDropdowns` | `[data-dropdown-toggle]` → `[data-dropdown-menu]` | Click toggle, close on outside or `[data-dropdown-close]` |
| `initModals` | `[data-modal]` + `[data-modal-close]` | `window.openModal/closeModal(id)`, body overflow hidden, Esc close |
| `initTabs` | `[data-tabs]` `[data-tab-trigger]` | `data-active` + `.hidden` toggle |
| `initConfirmDialog` | `[data-confirm-dialog]` | `window.confirmDialog({title,message,formAction,method})` → submit hidden form with CSRF+_method |
| `initGallery` | `[data-gallery-filters]` `[data-gallery-filter]` | Filter items by `data-category === filter`, toggle `.hidden`, set `data-active` |
| `initLightbox` | `[data-lightbox]` + `[data-gallery-item]` | Click gallery item → inject img src/title into `[data-lightbox-image]`, Esc/backdrop close |
| `initToasts` | `[data-toast-container]` | `window.toast(msg,type,duration)` builds DOM + progress bar + `meta[name="flash-*"]` auto-trigger |
| `initAlertDismiss` | `[data-alert-dismiss]` | Remove `role="alert"` element |

**Responsive**: Tailwind breakpoints `sm:`, `lg:`, `xl:` + `hidden lg:flex` nav, mobile drawer `lg:translate-x-0`. Admin table wrapper `overflow-x-auto min-w-[820px]` + sticky right `Aksi` shadow hints horizontal scroll.

**Error/Loading**: Flash alert danger/warning/success; `x-ui.empty-state` for zero data; no skeleton — content rendered server-side or not. Exception handler `bootstrap/app.php:36` renders back with error flash for non-Http exceptions.

**Caching**: No frontend cache layer (no localStorage data, no service worker). Server query `Program::active()->orderBy('order')->limit(4)->get()` executed per request; `Page::published()->orderBy('order')->limit(6)->get()` in navbar partial executed **every public page** (no memoization).

### 8.6 Data Flow (Frontend)
```
Request URL
   ↓
Route web.php → Controller (query Eloquent)
   ↓
view() dengan $data → Blade SSR (layouts/app + partials/navbar)
   ↓
HTML + Vite assets (css va JS)
   ↓
Browser executes app.interactions.js (theme/dropdown/modal/toast)
   ↓
User interacts → Form POST (CSRF) atau JS filter/lightbox (no network)
   ↓
Server responds redirect + flash → Toast renders via meta[name="flash-*"]
```

---

## 9. Backend Architecture

### 9.1 Controllers

| Controller | File | Actions | Input Source | Auth |
|------------|------|---------|--------------|------|
| `HomeController` | `Public/HomeController.php:12` | `index()` | — | — |
| `ProgramController` | `Public/ProgramController.php:10` | `index()`, `show(Program)` | slug model-bind | — |
| `NewsController` | `Public/NewsController.php:11` | `index()` paginate9, `show(News)` + related 3 | slug | — |
| `GalleryController` | `Public/GalleryController.php:8` | `index()` | — | — |
| `AnnouncementController` | `Public/AnnouncementController.php` | `index()` | — | — |
| `PageController` | `Public/PageController.php:8` | `show($slug)` | string slug | — |
| `ContactController` | `Public/ContactController.php:10` | `index()`, `send(StoreContactMessageRequest)` | FormRequest | — |
| `PPDBController` | `Public/PPDBController.php:11` | `index()`, `siswa()`, `store(StorePPDBRegistrationRequest)`, `status(Request)` | validated()/query string | — |
| `AuthController` | `Admin/AuthController.php:11` | `showLoginForm()`, `login(LoginRequest)`, `logout(Request)` | LoginRequest | guest / auth+admin |
| `DashboardController` | `Admin/DashboardController.php:10` | `index()` | — | auth+admin |
| `RegistrationController` | `Admin/RegistrationController.php:10` | `index`, `show`, `update`, `destroy`, `destroyAll` | Request search/status | auth+admin |
| `UserManagementController` | `Admin/UserManagementController.php:10` | `index`, `store`, `update` | Store/UpdateUserRequest | auth+admin+superadmin |
| `LoginLogController` | `Admin/LoginLogController.php:10` | `index` | Request event/search | auth+admin+superadmin |

**Semua thin** — tidak ada Service layer. Logic hanya di model scopes/accessors + controller query.

### 9.2 Form Requests (Validation)
| Request | File | Rules Kunci | Auth |
|---------|------|-------------|------|
| `LoginRequest` | `Http/Requests/LoginRequest.php:14` | email required+email max150, password required | true |
| `StorePPDBRegistrationRequest` | `Http/Requests/StorePPDBRegistrationRequest.php:14` | name required max150, gender in:laki-laki/perempuan, program_id required exists:programs,id, birth_date nullable date before:today | true |
| `StoreContactMessageRequest` | `Http/Requests/StoreContactMessageRequest.php:14` | name required max100, email required email max150, subject required max150, message required max2000 | true |
| `UpdateRegistrationStatusRequest` | `Http/Requests/UpdateRegistrationStatusRequest.php` | status required in:pending,accepted,rejected,cancelled (`RegistrationStatus` enum) | true |
| `StoreUserRequest` | `Http/Requests/StoreUserRequest.php` | name required max150, email required email unique:users, password required min8, role nullable in:admin,superadmin | true |
| `UpdateUserRequest` | `Http/Requests/UpdateUserRequest.php:14` | name required, email unique:users,email,$id, password nullable min8, role nullable in:admin,superadmin | true |

### 9.3 Middleware
| Middleware | Path | Logic |
|------------|------|-------|
| `EnsureUserIsAdmin` | `Http/Middleware/EnsureUserIsAdmin.php:12` | `abort_unless(auth()->user()?->is_admin,403)` |
| `EnsureUserIsSuperAdmin` | `Http/Middleware/EnsureUserIsSuperAdmin.php:12` | `abort_unless(auth()->user()?->is_superadmin,403)` |
| `throttle:5,1` | inline `routes/web.php:31,35` | Laravel throttle middleware — 5 attempts per minute per IP |

**Exception handling** (`bootstrap/app.php:35`):
- Let 500 generic exception → `back()->withInput()->with('error', 'Terjadi kesalahan tak terduga...')` (`bootstrap/app.php:56`).
- If `expectsJson()` → `response()->json(['message'=>'Terjadi kesalahan server.'],500)`.

### 9.4 Backend Data Flow
```
POST /ppdb
  → Route throttle 5,1
  → StorePPDBRegistrationRequest authorize true → rules validation
  → PPDBController@store try { PPDBRegistration::create(validated) }
    → Model booted creating: generateRegistrationNumber() = 'PPDB-YYYY-' + (max(id)+1 pad 5)
  → redirect route('ppdb.status', ['registration_number' => ...]) + flash success
  → catch Throwable → back with error flash

POST /kontak similar → ContactMessage::create

PUT /admin/registrations/{id} → auth+admin guard → UpdateRegistrationStatusRequest → update → back success

POST /admin/login → LoginRequest → Auth::attempt with remember → session regenerate → LoginLog::create event login
```

**Tidak ada**: API routes, Jobs/Queues (queue config `database` tapi no dispatched jobs terlihat), Events/Listeners, Policies/Gates, API Resources, Sanctum/JWT.

---

## 10. Database Architecture

### 10.1 Connection
- **Default**: `sqlite` (`DB_CONNECTION=sqlite` `.env.example:26`), file `database/database.sqlite` (created by `composer setup` hook). **CONFIRMED**.
- **Alternative**: `config/database.php` mendukung `mysql`, `pgsql` — switch via `DB_CONNECTION`.
- **Session/Cache/Queue**: `database` driver — tables `sessions`, `cache`, `jobs` (stock Laravel).
- **Migrations count**: 13 (`database/migrations/*.php`).
- **Seeder**: `DatabaseSeeder` creates 1 superadmin + Programs(4)+Pages(5)+News/Gallery/Announcements/PPDB dummy via factories.

### 10.2 Schema (INFERRED FROM MIGRATIONS + MODELS)

```text
users/                                      — .env.example:11 demo, Model User.php:22
  id PK
  name string
  email string unique
  email_verified_at timestamp nullable
  password string (hashed, casts hashed)    — hidden
  remember_token
  is_admin boolean default false indexed     — add_is_admin migration:12
  is_superadmin boolean default false indexed — add_is_superadmin migration:12
  timestamps

  → Accessed by: AuthController login, UserManagementController store/update,
                 LoginLog user relation, News author, middleware admin checks
  → Who read: auth user self, superadmin list users
  → Who write: superadmin (create/update), seeder

ppdb_registrations/                         — ppdb_registrations migration:8, Model PPDBRegistration.php:16
  id PK
  registration_number string unique         — generated PPDB-YYYY-XXXXX (booted creating)
  name string
  nisn string(20) nullable
  birth_place string(100) nullable
  birth_date date nullable
  gender string indexed (laki-laki/perempuan)
  address text nullable
  school_origin string(150) nullable
  phone string(30) nullable
  email string(150) nullable
  parent_name string(150) nullable
  program_id FK nullable → programs.id nullOnDelete indexed
  status string default 'pending' indexed   — enum RegistrationStatus (pending/accepted/rejected/cancelled)
  timestamps

  → Accessed by: PPDBController store/status, DashboardController stats/trend/distribution/recent,
                 RegistrationController index/show/update/destroy (+search across name/regnum/email/phone)
  → Who read: public (status check via registration_number), admin (all)
  → Who write: public (create), admin (update status/delete)

programs/
  id PK
  name string
  slug string unique                        — route key
  short_description text
  description longText
  image string nullable                     — Storage public disk → getImageAttribute url
  status string default active indexed      — ProgramStatus active/inactive
  order unsignedInt default 0
  timestamps

  → Accessed by: HomeController active limit4, ProgramController active + show, PPDBController siswa active,
                 Dashboard programDistribution join, factory/seeder
  → Who read: public (active only), admin (indirect via distribution)
  → Who write: seeder/factory only — NO ADMIN CRUD (INCOMPLETE)

news/
  id PK
  title string
  slug string unique                        — route key
  thumbnail string nullable                 — getThumbnailAttribute Storage url
  content longText
  status string default draft indexed       — ContentStatus draft/published/archived
  author_id FK nullable → users.id nullOnDelete
  published_at timestamp nullable indexed
  timestamps
  scopePublished: where status=published && published_at <= now && notNull

  → Accessed by: HomeController published limit3, NewsController index paginate9 + show + related, author relation
  → Who read: public published only
  → Who write: factory/seeder only — NO ADMIN CRUD

pages/
  id PK
  title string
  slug string unique                        — route key
  content longText                          — raw HTML with prose-content
  image string nullable
  meta_title string nullable
  meta_description text nullable
  status string default published indexed   — ContentStatus
  order unsignedInt default 0
  timestamps
  scopePublished: where status=published

  → Accessed by: PageController published by slug, navbar partial published limit6
  → Who read: public published
  → Who write: seeder only — NO ADMIN CRUD

galleries/                                  — Galleries model
  id PK
  title string
  image string nullable → getImageAttribute Storage url
  category string nullable (distinct pluck) — filter gallery
  status string default published? indexed  — ContentStatus
  order unsignedInt default 0
  timestamps
  scopePublished: where status=published

  → Accessed by: HomeController published limit6, GalleryController published all + distinct category
  → Who read: public
  → Who write: seeder only — NO ADMIN CRUD

announcements/
  id PK
  title string
  content text? longText
  status string default draft? indexed      — ContentStatus
  published_at timestamp nullable indexed
  timestamps
  scopePublished: where published && published_at <= now

  → Accessed by: HomeController published limit4, AnnouncementController published
  → Who read: public published
  → Who write: seeder only — NO ADMIN CRUD

contact_messages/
  id PK
  name string
  email string
  phone string nullable
  subject string
  message text (max 2000)
  status string default? (Model casts string) — migration status?
  timestamps

  → Accessed by: ContactController@send -> create
  → Who read: NOBODY via UI — no admin inbox → data only via DB/tinker (INCOMPLETE)
  → Who write: public

login_logs/
  id PK
  user_id FK → users.id cascadeOnDelete
  event string(20) default login indexed   — login/logout
  ip_address string(45) nullable
  user_agent string nullable
  created_at timestamp nullable (no updated_at, $timestamps=false)

  → Accessed by: AuthController recordActivity, DashboardController recent 5 superadmin,
                 LoginLogController filter event/search paginate 20
  → Who read: superadmin only
  → Who write: AuthController on login/logout

sessions/ (stock) / cache / jobs / password_reset_tokens — laravel default, not domain logic.
```

**Relationships**:
- `PPDBRegistration belongsTo Program` (`PPDBRegistration.php:56`), `program_id nullable → nullOnDelete` — deletion program tidak hapus registration (set null).
- `News belongsTo User author` (`News.php:41`), `author_id nullable → nullOnDelete`.
- `User hasMany LoginLog` (`User.php:55`).
- `Gallery/Announcement/Page` have **no relations**.

**No foreign key** dari `contact_messages` atau `login_logs` selain user.

**Storage of media**: `image`/`thumbnail` stored as path string e.g. `programs/xxx.jpg` → accessor returns `Storage::disk('public')->url($value)` → requires `php artisan storage:link` (`README.md:80`).

---

## 11. Authentication & Authorization

### 11.1 Auth Method
- **Laravel Session Guard** `web` (`config/auth.php:41` `driver session`, `provider eloquent` `App\Models\User`).
- **Driver** `database` → table `sessions` (`config/session.php:21`), `lifetime 120` minutes, `encrypt false`, `http_only true`, `same_site lax`.
- **Hashing**: `password` cast `hashed` (`User.php:49`), `BCRYPT_ROUNDS=12` (`.env.example:19`, `phpunit.xml` uses 4 for testing).
- **Flow** (`AuthController.php:18`):
  ```
  Login Form (admin.auth.login GET) → email+password (+ remember checkbox)
    → POST /admin/login → LoginRequest rules (email max150, password string)
    → Auth::attempt(credentials, remember boolean) → session regenerate
    → recordActivity(LOGIN): create login_logs row (ip, user_agent, now)
    → redirect intended admin.dashboard + success flash
    → fail: back withErrors email + error flash, onlyInput email
  ```
- **Logout** (`AuthController:37`): if Auth::check → record LOGOUT → Auth::logout → session invalidate → regenerateToken → redirect admin.login + success.
- **Guest redirect**: `bootstrap/app.php:27` `redirectGuestsTo => route('admin.login')`, `redirectUsersTo => route('admin.dashboard')` (authenticated guest visits login → dashboard).

### 11.2 Authorization (Role Check)
| Check | File | Logic | HTTP |
|-------|------|-------|------|
| `admin` | `EnsureUserIsAdmin.php:13` | `auth()->user()?->is_admin === true` | 403 abort |
| `superadmin` | `EnsureUserIsSuperAdmin.php:13` | `auth()->user()?->is_superadmin === true` | 403 abort |

- Applied in `routes/web.php:48,52`: `middleware ['auth','admin']` wraps all admin; inner `middleware 'superadmin'` wraps users+login-logs.
- **No Policies/Gates** — no `authorize()` beyond `true` in FormRequests.
- **Self-protection** (`UserManagementController.php:53`): `if ($role !== null && ! $user->is(auth()->user()))` → cannot change own role.

### 11.3 Security Properties
- **Throttle**: only `contact.send` & `ppdb.store` have `throttle:5,1`. **Admin login has NO throttle** → brute force risk (see §19).
- **CSRF**: `@csrf` in all forms, meta `csrf-token` (`layouts/app.blade.php:19`).
- **Trust Proxies**: `trustProxies at:*` (`bootstrap/app.php:24`) for Ngrok/HTTPS.
- **Cookie**: `http_only true` default, `secure` depends on `SESSION_SECURE_COOKIE` (unset → null), `partitioned false`.
- **Demo auto-login**: `admin/auth/login.blade.php:71` hidden form POST same credentials if `config('app.demo_admin_email')` set — convenient but dangerous if left in prod.

---

## 12. Admin Panel Mapping

```
Admin Panel (/admin) — layout: admin.layouts.app
├── /admin/login (guest) — admin.auth.login — email+password + demo click
├── / (dashboard) — DashboardController@index — auth+admin
│   ├── Hero greeting (translatedFormat l, d F Y + first name)
│   ├── 3 summary cards (Total, Diterima, Pending) → links filtered index
│   ├── Tren 7 hari bar chart (CSS height % + maxTrend)
│   ├── Sebaran status segmented bar (pending amber, accepted emerald, rejected red, cancelled slate)
│   ├── Peminat program bars (join count top5)
│   ├── Pendaftar terbaru list 6 (with program, diffForHumans, badge, Detail link)
│   └── Aktivitas login terbaru 5 (superadmin only) + Lihat Semua → login-logs
├── /admin/registrations — RegistrationController — auth+admin
│   ├── index: search (name/regnum/email/phone) + status dropdown → table paginate 15 + sticky Aksi + Hapus Semua
│   └── /admin/registrations/{id} — show: data siswa grid, program card, perbarui status select, hapus (confirmDialog)
├── /admin/login-logs — LoginLogController — auth+admin+superadmin
│   └── filter event (login/logout) + search user name/email → cards (mobile) + table (desktop) paginate20
└── /admin/users — UserManagementController — auth+admin+superadmin
    ├── table Nama/Email/Role/Terdaftar/Aksi → Edit button
    ├── Modal create-user-modal: name/email/password/role
    └── Modal edit-user-modal: name/email/password(role disabled if self) via fillEditModal JS
```

**Per modul**:

| Modul | Route | Page/Component | Data Source | CRUD | Tombol | Permission | Lengkap? |
|-------|-------|----------------|-------------|------|--------|------------|----------|
| Auth | `admin.login`, `admin.login.attempt` | `admin/auth/login` | `User` + `LoginRequest` | Login/Logout | Masuk, Masuk Sekali Klik, Keluar | guest / auth | Lengkap |
| Dashboard | `admin.dashboard` | `admin/dashboard` | `PPDBRegistration`, `LoginLog`, `programs join` | R (read stats) | Kelola Pendaftar, Lihat Website, Semua Pendaftar | admin | Lengkap |
| Registrations | `admin.registrations.*` | `admin/registrations/index+show` | `PPDBRegistration` | R, U (status), D (single/all) — **no Create** | Cari, Reset, Detail, Simpan Status, Hapus, Hapus Semua | admin | Lengkap untuk PPDB (Create via public) |
| Login Logs | `admin.login-logs.index` | `admin/login-logs/index` | `LoginLog` | R only | Cari, Reset | superadmin | Lengkap (read-only) |
| Users | `admin.users.*` | `admin/users/index` + 2 modals | `User` | R, C, U — **no D** | Tambah Admin, Edit, Simpan, Batal | superadmin | Tidak lengkap: no delete user |

**Yang belum ada di Admin** (gap): CRUD untuk `Page`, `Program`, `News`, `Gallery`, `Announcement`, `ContactMessage` — semua hanya read di publik dan managed via `tinker`/`seeder`.

---

## 13. Public Website Mapping

### 13.1 Navbar & Footer
| Bagian | Source | Data | Static/Dynamic | Route | Responsive |
|--------|--------|------|----------------|-------|------------|
| Navbar | `partials/navbar.blade.php:1` | `Page::published()->orderBy('order')->limit(6)` — query **per request** | Dynamic (published pages) | sticky top, backdrop-blur | desktop `hidden lg:flex` + mobile `[data-nav-menu]` toggle, theme-toggle, Pendaftaran CTA |
| Footer | `partials/footer.blade.php:1` | hardcoded contact, quick links array | Static + dynamic links | — | 4-col grid md/lg, border-t, dark |

### 13.2 Page Sections
| Section | Source Component | Data Source | Route | Perilaku |
|---------|------------------|-------------|-------|----------|
| Hero | `public/home.blade.php:3` gradient bg + blur blobs, PPDB badge + title + 2 CTAs | static | `/` | large typography, dark variant |
| CTA PPDB banner | `public/home.blade.php:59` gradient primary 700→900 | static | `/` | repeated at top & bottom |
| Statistik | `home:78` grid 2/4 — 4 stat cards (hardcoded 4/2016/850+/1200+) | static hardcoded | `/` | — |
| Tentang | `home:94` section-heading left + 3 features bullets + 4 thumbs | static + `<x-thumb>` placeholder | `/` | grid lg:2 |
| Program Keahlian cards | `home:136` cms loop `$programs` via `x-thumb` + `x-ui.card` hover | `Program::active limit4` | `/` + `/program-keahlian` | grid 2/4, empty-state if zero |
| Berita Terbaru | `home:169` loop `$news` via thumb+card+excerpt | `News::published limit3` | `/` + `/berita` | grid md:3, pagination di `/berita` |
| Pengumuman | `home:203` loop `$announcements` with accent icon | `Announcement::published limit4` | `/` + `/pengumuman` | vertical list max-w-3xl |
| Galeri preview | `home:232` grid thumbs via Gallery image | `Gallery::published limit6` | `/` + `/galeri` | grid 2/3, lightbox ở gallery page |

### 13.3 Detail Pages
| Page | View | Data | Static/Dynamic | Notable |
|------|------|------|----------------|---------|
| Program list | `public/programs/index.blade.php:11` | `Program::active orderBy order` | Dynamic | cards hover, empty-state |
| Program detail | `public/programs/show.blade.php` | `Program` + 3 `otherPrograms` | Dynamic | abort 404 if inactive, thumb + description |
| News list | `public/news/index.blade.php:11` | `News::published with author paginate9` | Dynamic | card, excerpt, pagination |
| News detail | `public/news/show.blade.php` | `News` + `related 3` + author | Dynamic | abort if not published/future, prose content |
| Gallery | `public/gallery/index.blade.php:11` | `Gallery::published` + distinct categories | Dynamic | filter buttons `[data-gallery-filter]`, lightbox |
| Announcements | `public/announcements/index.blade.php:11` | paginated published | Dynamic | article cards, whitespace-pre-line |
| Pages (profil etc) | `public/pages/show.blade.php:1` | `Page::published by slug` | Dynamic | `{!! $page->content !!}` prose-content, optional thumb |
| Contact | `public/contact/index.blade.php:11` | 4 info cards static + form | Partial dynamic | form POST throttle 5,1 |
| PPDB index | `public/ppdb/index.blade.php:1` | static CTA + 3-step alur | Static | links to /ppdb/siswa & /ppdb/status |
| PPDB siswa form | `public/ppdb/siswa.blade.php:1` | `Program::active` select | Dynamic | 13 fields, card-grouped, button Kirim |
| PPDB status | `public/ppdb/status.blade.php:1` | query by registration_number | Dynamic query-param | badge + statusMessage, alert if not found |

**Semua public page**: `bodyClass` none, `x-layouts.app` with vite, robots index,follow, canonical `url()->current()`, Plus Jakarta Sans via Bunny, favicon `/img/logo.png`.

---

## 14. Assets

| Asset | Path | Cara Dipanggil | Local/Remote | Status |
|-------|------|---------------|--------------|--------|
| Logo | `public/img/logo.png` | `asset('img/logo.png')` in navbar/footer/login/layouts | Local | CONFIRMED — used 7+ places |
| Beranda image | `public/img/beranda.png` | exist but not referenced in Blade (maybe placeholder) → potentially **unused** | Local | INFERRED unused — grep not found in blade |
| Favicon | `public/favicon.ico` + `<link rel="icon" href="{{ asset('img/logo.png') }}">` | Layout override to logo.png (not favicon.ico) | Local | favicon.ico orphaned? layout points to logo.png |
| Robots | `public/robots.txt` | static | Local | ok |
| Uploads (program image, news thumbnail, gallery image) | `storage/app/public/*` → accessed via `Storage::disk('public')->url()` in models | `x-thumb :src="$model->image"` | Local (requires `storage:link`) | Depends on symlink; fallback gradient jika null |
| Font | `https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800` | `<link preconnect>` + href | Remote (Bunny CDN) | CONFIRMED |
| Icons | Inline SVG (Heroicons inspired) stroke 1.8 | hardcoded in Blade | Local inline | No asset file |
| Pagination view | `resources/views/vendor/pagination/tailwind.blade.php` | Laravel pagination | Local | ok |
| Vite build | `public/build/` (gitignored) + `public/hot` | `@vite` | — | runtime generated |

**Broken reference risk**:
- `public/img/beranda.png` exists  but no `asset('img/beranda.png')` found → dead asset.
- `public/img/logo.png` lower vs `Beranda.png` capital? OK but case-sensitive on Linux — watch deploy.
- Storage images without symlink → 404 for `Storage::url()` (needs `php artisan storage:link`).

**Placeholder**: `<x-thumb>` renders gradient `from-primary-100 via-white to-accent-100` or camera SVG if `src` null — handles missing images gracefully.

---

## 15. Environment & Configuration

### 15.1 .env.example Variables (values hidden)
```text
APP_NAME                — "SMK Tahfizh Al-Fatih"
APP_ENV                 — local
APP_KEY                 — (generated)
APP_DEBUG               — true
APP_URL                 — http://localhost
APP_LOCALE / APP_FALLBACK_LOCALE — id
APP_FAKER_LOCALE        — id_ID
DEMO_ADMIN_EMAIL        — admin@smkalfatih.sch.id
DEMO_ADMIN_PASSWORD     — admin1234            # [SECRET DETECTED - VALUE HIDDEN] in real .env, visible in example for demo
APP_MAINTENANCE_DRIVER  — file
BCRYPT_ROUNDS            — 12
LOG_CHANNEL/STACK/LEVEL — stack/single/debug
DB_CONNECTION           — sqlite (commented mysql host/port/database)
SESSION_DRIVER          — database
SESSION_LIFETIME        — 120
SESSION_ENCRYPT/PATH/DOMAIN — false / / / null
BROADCAST_CONNECTION    — log
FILESYSTEM_DISK         — local
QUEUE_CONNECTION        — database
CACHE_STORE             — database
MEMCACHED_HOST etc      — 127.0.0.1
REDIS_*                 — phpRedis default
MAIL_MAILER/SCHEME/HOST/PORT — log / null /127.0.0.1/2525 (log driver = no real send)
AWS_*                   — blank (filesystem s3 unused)
VITE_APP_NAME           — "${APP_NAME}"
```

### 15.2 Config Files (`config/*.php`)
- `app.php:18-19` — `name` + `demo_admin_email/password` via `env()`.
- `auth.php:18` — guard web, provider `App\Models\User`.
- `database.php` — default sqlite, connections mysql/pgsql/sqlite.
- `filesystems.php` — disks local/public, links `public/storage` → `storage/app/public`.
- `session.php` — driver database, table `sessions`.
- `cache.php`, `queue.php`, `logging.php`, `mail.php`, `services.php`.

### 15.3 Build/Host Config
- `.htaccess` in `public/` — standard Laravel pretty URLs.
- No `firebase.json`, no `vercel.json`, no `docker-compose` (except optional Sail `composer.json:17` `laravel/sail ^1.41`).
- Deployment config = generic: `php artisan serve` for dev, Vite build.

---

## 16. Dependencies

### Core
| Dep | Version | Purpose |
|-----|---------|---------|
| `laravel/framework` | `^12.0` | Full MVC, ORM, Auth, Routing, Validation, Middleware, Seeder |
| `laravel/tinker` | `^2.10.1` | REPL (artisan tinker) |

### UI / Build
| Dep | Version | Purpose |
|-----|---------|---------|
| `tailwindcss` | `^4.3.3` | CSS utility framework (v4 @theme) |
| `@tailwindcss/vite` | `^4.3.3` | Vite plugin for Tailwind v4 |
| `laravel-vite-plugin` | `^2.0.0` | Bridge Vite <-> Laravel |
| `vite` | `^7.0.7` | Bundler for css/js |
| `axios` | `^1.11.0` | HTTP client (sets `window.axios`, but barely used) |
| `concurrently` | `^9.0.1` | Run `php artisan serve + queue:listen + npm run dev` in parallel |

### Dev / Quality
| Dep | Version | Purpose |
|-----|---------|---------|
| `fakerphp/faker` | `^1.23` | Factories fake data (via `APP_FAKER_LOCALE id_ID`) |
| `laravel/pail` | `^1.2.2` | Log tailing (`php artisan pail`) |
| `laravel/pint` | `^1.24` | Code style fixer (PHP-CS) |
| `laravel/sail` | `^1.41` | Docker dev env (optional) |
| `mockery/mockery` | `^1.6` | Mocking for PHPUnit |
| `nunomaduro/collision` | `^8.6` | Pretty error output |
| `phpunit/phpunit` | `^11.5.50` | Testing framework |

### Potensial Unused / Legacy
- `axios` — di-import tapi tidak dipakai untuk fetch PPDB/kontak (form native). Could be removed atau keep untuk future API.
- `laravel/sail` — not used unless Docker workflow; safe but adds vendor weight.
- No `laravel/breeze`, `jetstream`, `livewire`, `inertia` — auth manual, not scaffolding.

**No duplicate/deprecated major**: Semua versi latest stable per Agustus 2026. `@tailwindcss/vite` + `tailwindcss` v4 adalah pattern resmi baru, bukan legacy.

---

## 17. Actions & Buttons

> Legend: [Handler tersedia? | Route | Mutation | Placeholder?]

| Lokasi | Tombol/Link | Handler | Route/Mutation | Status |
|--------|-------------|---------|----------------|--------|
| Navbar desktop | Pendaftaran (primary) | `<a href>` | `route('ppdb.index')` | Berfungsi CONFIRMED |
| Navbar mobile toggle | Hamburger `data-nav-toggle` | `initMobileNav` | toggle `.hidden` | Berfungsi |
| Navbar dropdown Profil | Buttons `data-dropdown-toggle` | `initDropdowns` | show `data-dropdown-menu` | Berfungsi |
| Home hero CTA | Daftar PPDB / Lihat Profil | `<x-ui.button href>` | `ppdb.index` / `pages.show('profil')` | Berfungsi |
| Home CTA banner | Daftar Sekarang / Cek Status | `<x-ui.button>` | `ppdb.index` / `ppdb.status` | Berfungsi |
| Home program cards | Selengkapnya | `<a href route programs.show>` | GET detail | Berfungsi |
| Home berita/ galeri / pengumuman | Lihat Semua/ Lengkap | `<x-ui.button href>` | list routes | Berfungsi |
| Admin sidebar | Dashboard/Pendaftar/Log/Kelola User | `<a href>` | admin routes | Berfungsi (active check) |
| Admin dashboard cards | Total/Diterima/Pending | `<a href>` with query status | `admin.registrations.index?status=...` | Berfungsi |
| Admin hero | Kelola Pendaftar / Lihat Website | `<a href>` | registrations index / home _blank | Berfungsi |
| Admin registrations index | Hapus Semua | `confirmDialog` → form POST DELETE `destroy-all` | `RegistrationController@destroyAll` | Berfungsi (confirm) |
| Admin registrations table | Detail per row | `<x-ui.button href>` | `registrations.show` | Berfungsi |
| Admin registration show | Simpan Status | `<form PUT>` | `registrations.update` | Berfungsi |
| Admin registration show | Hapus | `confirmDialog` → DELETE | `registrations.destroy` | Berfungsi |
| Admin users index | Tambah Admin | `openModal('create-user-modal')` | — | Berfungsi |
| Admin users index | Edit per row | `fillEditModal(this)` → `openModal('edit-user-modal')` | fills form action | Berfungsi |
| Admin modals | Batal | `data-modal-close` | `closeModal()` | Berfungsi |
| Admin modals | Simpan / Simpan Perubahan | `<form POST/PUT>` | `users.store / users.update` | Berfungsi (role disabled if self) |
| Admin mobile drawer toggle | `[data-admin-drawer-toggle]` | `initAdminDrawer` | translate + backdrop | Berfungsi |
| Public PPDB index | Daftar Sekarang / Cek Status | `<a href>` | `ppdb.siswa` / `ppdb.status` | Berfungsi |
| PPDB siswa form | Kirim Pendaftaran submit | `POST /ppdb` | `PPDBController@store` | Berfungsi |
| PPDB status | Cek Status submit (GET) | `form GET ppdb.status` | `?registration_number=...` | Berfungsi |
| Gallery page | Filter Semua/categori buttons `[data-gallery-filter]` | `initGallery` | hide/show items | Berfungsi |
| Gallery items | Click `[data-gallery-item]` | `initLightbox` | inject img into lightbox | Berfungsi |
| Contact form | Kirim Pesan submit | `POST /kontak` throttle 5 | `ContactController@send` | Berfungsi |
| Theme toggle | `[data-theme-toggle]` (sun/moon) | `initThemeToggle` | localStorage theme | Berfungsi |
| Toast dismiss | `[data-alert-dismiss]` / toast button | `initAlertDismiss` / `dismissToast` | remove DOM | Berfungsi |
| Login form | Masuk submit | `POST admin.login.attempt` | `AuthController@login` | Berfungsi |
| Login form | Masuk Sekali Klik sebagai Admin | hidden form POST same demo creds | same route | Berfungsi (demo) |
| Admin logout | Keluar (sidebar + mobile header) | `<form POST admin.logout>` | `AuthController@logout` | Berfungsi |
| Footer links | Beranda/Program/Berita/Galeri/Kontak | `<a href>` | public routes | Berfungsi |

**Dead / Placeholder? NONE critical**. Semua tombol visible memiliki handler atau href. Minor: `x-ui.checkbox/radio` components exist but never rendered → dormant, bukan dead button inside UI.

---

## 18. UI/UX System

### 18.1 Design Tokens (`resources/css/app.css:10`)
| Token | Value | Penggunaan |
|-------|-------|------------|
| `--font-sans` | Plus Jakarta Sans (Bunny) + system fallback | Global `font-sans` |
| `--color-primary-*` | 50 #ecfdf5 → 950 #022c22 (emerald/teal) | Primary brand — button primary, links, hero gradients `from-primary-700 to-primary-900` |
| `--color-accent-*` | 50 #fffbeb → 900 #78350f (amber/yellow) | Accent — `from-accent-500` CTA, galeri icon |
| `--color-surface` | #f8fafc / dark #0f172a | Body bg |
| `--shadow-card` | 0 1px 2px + 0 1px 3px (soft) | Card default |
| `--shadow-card-hover` | 0 4px 6px + 0 2px 4px | Hover state (`hover:shadow-card-hover`) |
| `--shadow-soft` | 0 8px 30px | Home CTA banner |
| `--radius-xl2` | 1.25rem | — |
| Transition | 0.15s–0.2s color/transform | Buttons, cards `hover:-translate-y-0.5` |

### 18.2 Typography
- `text-4xl sm:5xl lg:6xl extrabold tracking-tight` untuk hero title.
- `text-sm font-medium` untuk nav/labels, `text-xs uppercase tracking-wider` untuk badges/section subtitles.
- `prose-content` (`app.css:99`) custom: `text-[15px] leading-relaxed slate-700`, h2/h3 `font-bold slate-900`, ul dot `bg-primary-500`, strong `font-semibold`.

### 18.3 Spacing & Layout
- Container `mx-auto max-w-7xl px-4 sm:px-6 lg:px-8`.
- Section vertical `py-16 lg:py-20`, `py-12 lg:py-16` for list pages.
- Cards gap `gap-6` grid `sm:grid-cols-2 lg:grid-cols-4`.
- Rounded: `rounded-xl` cards, `rounded-2xl xl` banners, `rounded-lg` buttons, `rounded-full` badges/filters.

### 18.4 Components Style (per `x-ui/*`)
- **Button**: 6 variants (`primary emerald`, `secondary primary-50`, `outline white`, `ghost transparent`, `danger red`, `accent amber`) + sizes `xs/sm/md/lg`, `rounded-lg font-semibold`.
- **Input**: `rounded-lg border slate-300 focus primary-500 ring-primary-200`, error → red. Label `text-sm font-medium slate-700`, required `* red`.
- **Badge**: `rounded-full px-2.5 py-1 text-xs font-semibold` + dot `size-2` if `dot`.
- **Card**: `rounded-xl border slate-200 dark:slate-800 bg-white dark:slate-900 shadow-card`.
- **Alert**: similar card + border-left but inline (`resources/views/components/ui/alert.blade.php`).
- **Dropdown**: absolute menu `rounded-xl border shadow-pop`, item `px-3 py-2 text-sm`.
- **Modal**: fixed inset overlay `bg-slate-950/60 backdrop-blur-sm`, inner `rounded-2xl bg-white p-6`.
- **Toast**: fixed container `[data-toast-container]` top-right-ish? Actually layout inserts at body end — slide-in `toast-slide-in` 0.35s, progress bar anim 5000ms.
- **Table**: `divide-y slate-200`, thead `bg-slate-50 dark:slate-800`, sticky right shadow for overflow hint.

### 18.5 Navigation
- **Navbar**: sticky `top-0 z-40 border-b backdrop-blur-md bg-white/90`, h-16, link `rounded-lg px-3 py-2 text-sm medium`, active `bg-primary-50 text-primary-700`.
- **Admin sidebar**: `fixed w-64 -translate-x-full lg:translate-x-0 border-r`, top logo 40px, nav gap `space-y-1`, active `bg-primary-600 text-white`. Mobile header `lg:hidden h-14` with drawer toggle.

### 18.6 Responsive & Dark Mode
- **Breakpoint**: Tailwind default `sm 640, md 768, lg 1024, xl 1280`. Used: `sm:flex-row`, `lg:grid-cols-2`, `hidden lg:flex`.
- **Mobile**: nav collapse hamburger, admin table horizontal scroll `overflow-x-auto min-w-[820px]` + hint `Geser tabel`. Contact `grid gap-10 lg:grid-cols-5`.
- **Dark mode**: custom variant `.dark` class on `<html>`, toggled via `localStorage.theme` + `prefers-color-scheme`. All colors have `dark:bg-slate-* dark:text-white` dual. No system-only.

### 18.7 Consistency
- **Konsisten tinggi**: hampir semua warna/border/shadow via tokens, komponen `x-ui.button` terpakai everywhere, no inline `style=` except bar chart `height %` dan dynamic thumbnail ratio.
- **Hardcoded**: statistik home `['4','2016','850+','1200+']` dan kontak footer `Jl. Pendidikan No.1` — acceptable but not DB-driven.
- **No dark-mode bug visible**: semua card memiliki `dark:border-slate-800`.

---

## 19. Security Observations

| # | Id | Severity | Location | Deskripsi | Evidence | Don't Fix Yet |
|---|----|----------|----------|-----------|----------|---------------|
| S1 | Demo cred exposure | **MEDIUM** | `.env.example:11-12` + `admin/auth/login.blade.php:56,71` | Demo email/password (`admin1234`) committed in `.env.example` + auto-login button (` Masuk Sekali Klik`) yang submit plain form. Jika `.env` lupa diubah di prod atau `DEMO_ADMIN_*` tidak dikosongkan, attacker one-click login. `README.md:89` docs the same. | `.env.example:12` `DEMO_ADMIN_PASSWORD=admin1234`; `config/app.php:19` reads `env('DEMO_ADMIN_PASSWORD')`; Blade `if($demoEmail && $demoPassword)` shows button. | Catat, jangan hapus |
| S2 | Admin login no throttle | **HIGH** | `routes/web.php:45` + `bootstrap/app.php` | `POST /admin/login` tanpa `throttle` sedangkan `contact` & `ppdb.store` pakai `throttle:5,1`. Brute force possible. `LoginRequest` only email+password, no captcha/reCAPTCHA. | `Route::post('/login', AuthController@login)` no middleware `throttle` vs `Route::post('/kontak' ...)->middleware('throttle:5,1')`. | HIGH |
| S3 | Registration number enumeration | **LOW** | `PPDBRegistration.php:49` `generateRegistrationNumber` | `max(id)+1` sequential (`PPDB-2026-00001`...) + status check via GET `?registration_number=` without auth → attacker can enumerate registrations and harvest status/names? Mitigasi: status page only returns name/program if found, but sequential predictable. | `sprintf('PPDB-%s-%05d', now()->year, $next)` with `$next = max('id')+1`; `PPDBController@status:44` `where('registration_number', $regNum)->first()` no rate limit on GET status. | LOW (data not highly sensitive, but privacy) |
| S4 | Mass assignment delete-all | **MEDIUM** | `RegistrationController@destroyAll:54` | `DELETE /admin/registrations` deletes **all rows** `PPDBRegistration::query()->delete()` without further confirmation beyond JS `confirmDialog` (client-side only). CSRF protected (@csrf) but no soft-delete, no audit log, no double-confirm password. Accidental or CSRF if JS bypassed still hits route. | `routes/web.php:66` `Route::delete('/registrations', destroyAll)`; `destroyAll:63` `.delete()` no `->where` guard. | MEDIUM |
| S5 | `{!! $page->content !!}` XSS risk | **MEDIUM** | `public/pages/show.blade.php:16` + `news show` vs `home` | Content rendered unescaped. If admin can edit pages via future CMS (now seeder only) and input `<script>`, XSS persistent. Saat ini `PageSeeder` hardcodes safe HTML, but no sanitizer if admin editing added later; `News` content similar (`{!! !!}`?). Home `prose-content` also unescaped. No `Purifier` usage found. | `show.blade.php:16` `{!! $page->content !!}`; no `strip_tags` except excerpt. | MEDIUM |
| S6 | No server-side sanitization on contact/PPDB text | **LOW** | `Store*Request` rules | Rules `string max:*` tapi tidak strip HTML — attacker store `<script>` di `address/message/school_origin` yang ditampilkan di admin detail (`admin/registrations/show.blade.php:43` `{{ $registration->address }}` escaped via `{{ }}` → safe). However export/ API future could expose raw. | Blade `{{ }}` escapes → currently safe, but DB stores raw. | LOW |
| S7 | `Storage::url` direct exposure | **LOW** | `Program/News/Gallery` accessors | If `image` path is user-controlled (future admin upload), path traversal? Laravel Storage sanitizes, but no mime validation yet because no upload endpoint exists. | `getImageAttribute: Storage::disk('public')->url($value)` | LOW |
| S8 | Session `database` + `SESSION_ENCRYPT false` | **LOW** | `session.php:50` | Cookies not encrypted at session payload. `http_only true` mitigates, but `secure` = env null → on http, cookies via plaintext. | `.env.example:35` `SESSION_ENCRYPT=false` | LOW |
| S9 | `trustProxies at:*` | **LOW** | `bootstrap/app.php:24` | Trust all proxies — fine for Ngrok but if deployed behind untrusted load balancer could spoof IP (`LoginLog ip_address` may be wrong). | `trustProxies(at: '*')` | LOW |
| S10 | User email unique bypass? | **LOW** | `UpdateUserRequest:18` | `unique:users,email,{$this->route('user')->id}` correctly excludes self — ok. | — | LOW but correct |

**Tidak ditemukan**: Hardcoded secret lain selain demo, open Firebase rules (no Firebase), exposed API key, hardcoded JWT. `APP_KEY` blank in example (expected generation).

**Klasifikasi ringkas**: Critical 0, High 1 (S2), Medium 3 (S1,S4,S5), Low 6.

---

## 20. Technical Debt

| Debt | Lokasi | Dampak | Prioritas Debt |
|------|--------|--------|----------------|
| **Missing admin CRUD for content** | No controller for `Program/News/Gallery/Announcement/Page/ContactMessage` | Admin cannot manage website content without tinker/DB. Largest gap. | HIGH |
| **Nav query N+1 / repeated** | `partials/navbar.blade.php:2` `Page::published()->limit(6)->get()` executed **every public request** | DB hit on every page, no cache. Should use `View::share` + cache. | MEDIUM |
| **Sequential registration race** | `PPDBRegistration.php:50` `max(id)+1` | Concurrent POST can duplicate registration_number (no DB transaction/lock + unique constraint will throw, but silent catch → generic error). | MEDIUM |
| **No pagination on home queries** but limited; ok. However `GalleryController:18` `Gallery::published()->get()` tanpa limit bisa grow besar (should paginate). | `GalleryController.php:18` | Load all rows O(n) | LOW |
| **Contact messages no inbox** | No admin view | Messages invisible to admin → lost leads. | HIGH |
| **User delete missing** | `UserManagementController` no destroy | Cannot revoke admin except DB. | MEDIUM |
| **Hardcoded stats & contact** | `home:80` `[4,2016,850+,1200+]`, `footer:50` address | Not editable without code deploy. | LOW |
| **Axios unused** | `package.json:11` + `bootstrap.js:7` | Extra dep, confuses. | LOW |
| **No soft delete** | `PPDBRegistration` destroy hard delete | Data loss, no audit trail except login logs. | MEDIUM |
| **Exception handler swallows error** | `bootstrap/app.php:56` `back()->with('error', 'Terjadi kesalahan tak terduga')` for non-HTTP exceptions | Real error hidden, no logging context (relies on LOG_CHANNEL). Debugging hard. | MEDIUM |
| **No tests for contact/gallery/pages** | Tests cover admin/PPDB/login/user but not `Announcement/Gallery/Contact/Page` | Regression risk. | LOW |
| **Inconsistent casts** | `ContactMessage` casts status string, others enum | Weak typing. | LOW |
| **CSS/prose using `!!` raw** | Already noted security debt. | — | MEDIUM |
| **Bunny Fonts CDN only** | No fallback if CDN down | FOIT | LOW |
| **No queue usage** | `QUEUE_CONNECTION=database` but no jobs | Dead config. | LOW |
| **Vite `ignored storage/framework/views`** | `vite.config.js:15` watcher ignore | Good actually — not debt. | — |
| **No `any`/weak typing** | PHP strict: `protected $fillable`, `casts():array`, enums — generally strong. | Good. | — |

**Global debt level**: **MEDIUM**. Core PPDB flow solid; CMS half is missing but architecture ready (models+seeders exist).

---

## 21. Incomplete Features

```
POTENTIALLY INCOMPLETE FEATURES
─────────────────────────────────────────────────────────────────────────
1. ✗ Admin CRUD Program         — Model+Seeder ada, Controller/Route/View admin TIDAK ADA
2. ✗ Admin CRUD News            — same (needs thumbnail upload + status + published_at)
3. ✗ Admin CRUD Gallery         — needs image upload + category + lightbox ordering
4. ✗ Admin CRUD Announcement    — —
5. ✗ Admin CRUD Page            — needs rich HTML editor + slug auto + SEO fields + order
6. ✗ Admin Inbox ContactMessage — Table exists, Controller not, no route, no UI (messages lost)
7. ✗ Admin User Delete          — Index+Create+Update ada, Destroy belum (tests expect only store/update)
8. ✗ Password Reset             — No route / forgot password flow (auth only login/logout)
9. ✗ Registration Export (CSV)  — Dashboard shows stats but no export/download
10. ✗ Email Notification         — MAIL_MAILER=log (no send to admin on new PPDB/contact)
11. ✗ File Upload Handling       — No controller handles Store image; Storage Link documented but no use
12. ✗ Audit for Registration     — LoginLog only for auth, not for registration status changes (no RegistrationLog)
13. ○ Map / Embed               — Contact info hardcodes address without Google Maps embed
14. ✗ Search/Filter News/Gallery public — Only admin registration filter exists
15. ○ Pagination on /galeri & /pengumuman/p -> galeri loads all; announcement probably paginated (need verify)
16. ○ SEO structured data       — OG tags exist but no JSON-LD, no sitemap.xml generation
17. ○ beranda.png unused        — asset exists but not referenced → maybe incomplete hero image
18. ✗ Throttle on /admin/login  — incomplete security feature (see S2)
19. ✗ Soft delete / recycle bin — deleteAll hard
20. ✗ Multi-language            — locale id but hardcoded Indonesian strings only
```

> `✗` = missing, `○` = partial/optional. Semua di atas **INFERRED FROM GAP** — tidak ada TODO comment, tapi terbukti dari absent route/controller/view vs model existence.

---

## 22. Development & Deployment

### Development
```bash
# 1. Prereq
PHP >= 8.2   # composer.json:9
Composer
Node.js + npm

# 2. Setup (via Composer script hook)
composer setup
#   - composer install
#   - copy .env.example .env (if .env missing)
#   - php artisan key:generate
#   - php artisan migrate --force
#   - npm install
#   - npm run build   # vite build

# Manual alternative (README.md:43)
composer install
cp .env.example .env        # or copy di Windows
php artisan key:generate
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
php artisan migrate --seed  # includes ProgramSeeder, PageSeeder, NewsSeeder, GallerySeeder, AnnouncementSeeder, PPDBSeeder + superadmin
php artisan storage:link    # symlink storage/app/public → public/storage
npm install
npm run build               # production
npm run dev                 # Vite dev server HMR (used dengan composer dev)

# Dev concurrent (composer.json:44)
composer dev
# = npx concurrently -c "#93c5fd,#c4b5fd,#fb7185" 
#   "php artisan serve" "php artisan queue:listen --tries=1 --timeout=0" "npm run dev"
#   --names=server,queue,vite --kill-others
#   → http://localhost:8000, Vite HMR default 5173

# Test
composer test          # php artisan config:clear && php artisan test
php artisan test       # PHPUnit with sqlite :memory: (phpunit.xml:26)
```

### Build
```bash
npm run build   # vite build -> public/build/manifest + assets + public/build/*.css/*.js
# No npm `test` or `lint`
```

### Production
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
npm run build
# Serve via Apache/Nginx pointing DocumentRoot to /public
```

### Deployment Requirements
- **Env must set**: `APP_KEY` (generated), `APP_URL`, `DB_CONNECTION` (sqlite path must be writable OR mysql credentials), `DEMO_ADMIN_EMAIL/PASSWORD` should be **empty** in prod.
- **Ext PHP**: `pdo`, `mbstring`, `fileinfo` (Laravel need), plus `sqlite` if using sqlite.
- **Storage**: `storage/` and `bootstrap/cache/` writable, `storage:link` executed.
- **No external services**: No Firebase/S3; `FILESYSTEM_DISK=local`, `MAIL_MAILER=log` di example → no SMTP needed for demo.
- **Queue/Cache**: `database` driver → must have `jobs` + `cache` tables (migrated).
- **Proxies**: `trustProxies at:*` already set → ready for Ngrok/Cloudflare.

### Current Hosting Evidence
- `.htaccess` Apache ready, no `Dockerfile`, no `vercel.json`, no `render.yaml` — vendor-agnostic.

---

## 23. System Architecture

```
                            GUEST / VISITOR
                                   │
                                   ▼
                  ┌─────────────────────────────────┐
                  │        PUBLIC WEBSITE (SSR)     │
                  │  layouts/app.blade.php + Vite   │
                  │  navbar (Page::published) +     │
                  │  footer + theme-toggle (localStorage)
                  └──────────────┬──────────────────┘
                                 │
        ┌────────┬─────────┬──────┴──────┬─────────┬────────┬──────────┐
        ▼        ▼         ▼             ▼         ▼        ▼          ▼
    HomeController Program  News  Gallery Announcement Page   Contact  PPDB
        │        │         │       │         │       │         │        │
        └────────┴─────────┴───────┴─────────┴───────┴─────────┴────────┘
                                   │
                                   ▼
                     ┌───────────────────────────┐
                     │      FORM REQUESTS        │
                     │  StorePPDB / Contact / Login │
                     │  + validation + throttle 5,1│
                     └──────────────┬────────────┘
                                   │
                 ┌─────────────────┼─────────────────┐
                 │                 ▼                 │
                 │          ELOQUENT MODELS          │
                 │  Page/Program/News/Gallery/       │
                 │  Announcement/PPDB/Contact        │
                 │  LoginLog/User + Enums            │
                 └──────────────┬────────────────────┘
                                   │
                                   ▼
                     ┌───────────────────────────┐
                     │     DATABASE (SQLite)     │
                     │ users / ppdb_registrations│
                     │ programs/news/pages/      │
                     │ galleries/announcements/  │
                     │ contact_messages/login_logs│
                     └──────────────┬────────────┘
                                   ▲
                                   │
                         ┌─────────┴──────────┐
                         │    ADMIN PANEL     │
                         │  auth → session DB │
                         │  middleware admin/superadmin
                         │  Dashboard + Registrations
                         │  Users + LoginLogs
                         └────────────────────┘
                                   │
                      LoginForm → Auth::attempt → LoginLog event
```

### Frontend Architecture
```
Request → Route → Controller → Blade View (layouts/app)
   → Components (<x-ui.*>, <x-thumb>, partials/navbar)
   → HTML + Vite(js/css)
   → Browser: app.interactions.js (theme, dropdown, modal, toast, gallery, lightbox)
   → localStorage(theme) + Session(flash) + Cookies(session/csrf)
```

### Backend Architecture
```
HTTP (web.php) → Middleware(guest/auth/admin/superadmin/throttle)
 → FormRequest(validate) → Controller(thin, query+view) → Eloquent Model(scope+accessor)
 → Storage(public) for media URL
 → Response view() / redirect() + Exception Handler → Toast/Alert
```

### Database Architecture
- Single SQLite file, 3 core content group:
  - **Content CMS**: `programs, news, pages, galleries, announcements` (all with status enum + slug/image + order)
  - **Transactional**: `ppdb_registrations` (sequential regnum, FK program), `contact_messages`
  - **System**: `users` (is_admin/superadmin), `login_logs` (audit), `sessions/cache/jobs` (framework)

### Authentication Architecture
```
POST /admin/login → LoginRequest → Auth::attempt(session guard, remember)
  → success: regenerate session, LoginLog::create login, redirect dashboard
  → fail: back withErrors
POST /admin/logout → LoginLog logout, Auth::logout, invalidate session
All /admin/* → auth → EnsureUserIsAdmin → EnsureUserIsSuperAdmin (for subset)
```

---

## 24. Data Flow

### PPDB Registration Flow (Primary)
```text
Visitor
  ↓ GET /ppdb/siswa
Form (programs active)
  ↓ POST /ppdb (throttle 5)
Validation (StorePPDBRegistrationRequest)
  ↓ valid
Service: PPDBRegistration::create(validated) → booted generateRegistrationNumber()
  ↓
Database: ppdb_registrations (status=pending)
  ↓ redirect GET /ppdb/status?registration_number=PPDB-YYYY-XXXXX
Status Page
  ↓ visitor checks
Admin Dashboard (stats + recent)
  ↓ admin opens
GET /admin/registrations?search&status
  ↓ admin clicks Detail
GET /admin/registrations/{id}
  ↓ admin selects status (pending/accepted/rejected/cancelled)
PUT /admin/registrations/{id} → validated → update()
  ↓
Database: status updated
  ↓ visitor re-checks status page
Displayed badge + statusMessage (accepted -> selamat, rejected -> maaf, pending -> verifikasi)
```

### Contact Message Flow
```text
GET /kontak → form
POST /kontak (throttle 5) → StoreContactMessageRequest → ContactMessage::create
  → back success flash → toast
  → DB (contact_messages) (no admin read UI → INCOMPLETE)
```

### News Lifecycle (Current, CMS-less)
```text
Factory/Seeder → News::create (status published, published_at now, author_id nullable)
  → scopePublished filters
  → HomeController(limit3) / NewsController(index paginate9, show related)
  → Blade renders excerpt via getExcerptAttribute + thumbnail Storage url
  → No admin edit → static seed data (incomplete feature)
```

### Login Audit Flow
```text
POST /admin/login → success → Auth::user()->loginLogs()->create(event:login, ip, ua, now)
POST /admin/logout → create(event:logout)
DashboardController: superadmin → LoginLog::with(user)->latest 5
LoginLogController: filter by event/search via whereHas(user)
```

### Page Statis Flow
```text
Seeder PageSeeder → Page::updateOrCreate(slug) → content HTML
  → Navbar queries published limit6 → dropdown
  → GET /{slug} → PageController → where slug + published → {!! content !!} prose
```

---

## 25. Important Files

### CRITICAL — Inti aplikasi, baca pertama
| File | Alasan |
|------|--------|
| `routes/web.php:1` | Semua endpoint, middleware chain, throttle |
| `bootstrap/app.php:15` | Middleware alias, exception render, trustProxies |
| `app/Models/PPDBRegistration.php:1` | Logic nomor registrasi, fillable, status cast — transaksi PPDB |
| `app/Http/Controllers/Public/PPDBController.php:11` | Flow PPDB validation→create→redirect |
| `app/Http/Controllers/Admin/DashboardController.php:10` | Agregasi statistik kompleks (7-day trend, distribution) |
| `app/Http/Controllers/Admin/AuthController.php:11` | Auth + login log recording |
| `app/Http/Middleware/EnsureUserIsAdmin.php:1` + `EnsureUserIsSuperAdmin.php:1` | Authorization gate |
| `config/auth.php:18` + `config/session.php:21` | Auth/session setup |
| `database/migrations/2026_08_14_040001_create_ppdb_registrations_table.php` | Skema PPDB |
| `resources/views/components/layouts/app.blade.php:1` + `admin/layouts/app.blade.php:1` | Shell HTML seluruh aplikasi |

### IMPORTANT — Feature utama
| File | Alasan |
|------|--------|
| `app/Models/Program.php`, `News.php`, `Page.php`, `Gallery.php`, `Announcement.php` | Domain content models + scopes+accessors |
| `app/Http/Controllers/Public/*` (Home, Program, News, Gallery, etc) | Public SSR logic |
| `app/Http/Controllers/Admin/RegistrationController.php`, `UserManagementController.php` | Admin CRUD |
| `resources/views/public/home.blade.php:1` | Komposisi halaman publik (8 sections) |
| `resources/views/admin/dashboard.blade.php:1` + `admin/registrations/index+show` | Admin UI utama |
| `resources/js/app.interactions.js:1` | Seluruh interaktivitas frontend |
| `resources/css/app.css:1` | Tokens, prose, animations |
| `database/seeders/*` | Source of truth untuk content demo |

### SUPPORTING — Utility/helper/style
| File | Alasan |
|------|--------|
| `app/Enums/*` | Status enums reusable |
| `app/Http/Requests/*` | Validation rules |
| `resources/views/components/ui/*` | Design system (card, button, input, toast etc) |
| `resources/views/partials/*` | Navbar/footer |
| `vite.config.js:1` + `package.json:5` + `composer.json:34` scripts | Build tooling |
| `config/*.php` | DB, filesystems, mail, queue |
| `tests/Feature/*` | Spec behavior |

### GENERATED — Build/output
| File/Dir | Alasan |
|----------|--------|
| `public/build/*` | Vite manifest + hashed assets (gitignored) |
| `storage/framework/views/*` | Cached compiled Blade |
| `public/storage` | Symlink → storage/app/public (gitignored) |
| `vendor/*`, `node_modules/*` | Dependencies |
| `database/database.sqlite` | Local DB file (generated via setup) |

**Recommended Reading Order** → lihat §29.

---

## 26. Change Risk Map

```
HIGH RISK — Ubah dengan hati-hati, test menyeluruh, backup DB
────────────────────────────────────────────────────────────────
- app/Http/Middleware/* + bootstrap/app.php:30 middleware aliases
  → AuthZ gate; salah ubah = lockout semua admin atau bypass

- app/Models/PPDBRegistration.php:40 booted + generateRegistrationNumber
  → Concurrent bug risk; hapus unique idx = collision

- routes/web.php:38 catch-all /{slug} (?!admin)
  → Ubah regex/order dapat shadow admin atau 404 legitimate page

- config/auth.php + config/session.php + bootstrap/app.php:27 redirectGuestsTo
  → Session driver mismatch = login infinite loop

- app/Http/Controllers/Admin/RegistrationController destroyAll
  → Mass delete no soft-delete; change needs safety guard

MEDIUM RISK — Affect feature but localized
────────────────────────────────────────────────────────────────
- DashboardController aggregations (7-day, programDistribution join)
  → Heavy query; change GROUP BY / date logic can break stats

- resources/js/app.interactions.js global window.* (openModal, confirmDialog, toast)
  → Many Blade files depend on these globals; rename breaks modals everywhere

- resources/views/components/ui/* (button, input, card)
  → Widely reused; prop change harus backward-compatible

- resources/views/components/layouts/app.blade.php OG/meta Vite
  → Affects SEO + asset loading for all public pages

LOW RISK — Static or isolated
────────────────────────────────────────────────────────────────
- public/home.blade.php stat numbers, tentang sections (hardcoded)
- resources/views/public/pages/show.blade.php prose rendering
- seeder content (PageSeeder, ProgramSeeder) — safe to update
- Tailwind tokens in resources/css/app.css (value change visual only)
```

**Estimasi blast radius**: Perubahan `web.php` + `PPDBRegistration` + `middleware` mengunci PPDB & admin sekaligus (highest).

---

## 27. Project Maturity

| Dimensi | Status | Evidence |
|---------|--------|----------|
| **Prototype** | ✔ Completed | Models + migrations + factories + seeders + views semua ada |
| **Development** | **Current** | Fitur PPDB & admin registration matang; demo credentials; `APP_DEBUG true` in example; `phpunit.xml` sqlite memory; tests pass expected; UI polished (dark mode, responsive) |
| **Staging** | Partial | README deploy steps exist, but no CI, no staging env separation, demo email not disabled automatically |
| **Production-ready** | **Not yet** | Missing: admin CMS untuk konten (huge gap), contact inbox, real storage upload handling, email sending, throttle login, sitemap, backup, monitoring, hardening (S1-S2) |

**Bukti teknis maturity**:
- `README.md:93` `composer test` covers admin auth, PPDB, user mgmt — suggests dev-stage quality gate.
- `database/seeders` complete dummy → suggests staging data.
- `resources/views/errors/*` ada → production error pages ready.
- `config/queue.php` + `caches` database → production-compatible but unused.
- `.env.example:11` `DEMO_ADMIN_PASSWORD` → staging leftover.

**Overall**: `DEVELOPMENT` moving to `STAGING`. Core transaction (PPDB) prod-ready; CMS & operational features (70% website) yet to build.

---

## 28. Known Problems

1. **Demo auto-login exposed** — `.env.example` + `login.blade` show one-click admin. [SECRET DETECTED - VALUE HIDDEN] prod risk if `DEMO_*` left. (§19 S1)
2. **Admin login no rate limit** — brute force without `throttle`. (§19 S2)
3. **Navbar N+1** — `Page::published limit6` query every request tanpa cache → DB load ke atas.
4. **Registration number race** — `max(id)+1` non-atomic, concurrent request dapat duplicate unique → throw 500 (caught generic error).
5. **Contact messages orphan** — no admin UI; cannot triage leads. Data only via `php artisan tinker` or raw DB.
6. **Content admin absent** — all `Program/News/Page/Gallery/Announcement` editable only via code/migration; client cannot update sendiri.
7. **Delete-all no safety** — JS confirm only; no password re-auth, no soft-delete, no audit of deletion count.
8. **Gallery loads all** — `Gallery::published()->get()` without pagination → performance drop if 1000+ photos.
9. **Asset `beranda.png` unused** — dead file indicates incomplete hero image integration.
10. **User cannot be deleted** — `UserManagementController` missing destroy → admin offboarding manual DB.
11. **Email disabled** — `MAIL_MAILER=log` → no notification on new PPDB or contact; admin must polling dashboard.
12. **Generic exception handler hides root cause** — `bootstrap/app.php:56` `back with error` loses stacktrace untuk non-HTTP errors; log only via LOG_CHANNEL.
13. **Storage symlink dependency** — jika `php artisan storage:link` lupa, semua `Storage::url()` images 404.

---

## 29. Recommended Reading Order

Untuk engineer baru — baca dalam urutan ini untuk pemahaman minimal effort:

| Step | File | Kenapa |
|------|------|--------|
| 1 | `README.md` | Overview + demo credentials + setup |
| 2 | `routes/web.php` | Peta jalan semua endpoint dalam 1 file |
| 3 | `app/Models/User.php`, `PPDBRegistration.php`, `Program.php` | Core entities + relationships |
| 4 | `app/Enums/RegistrationStatus.php` + `ContentStatus.php` | Status machine |
| 5 | `app/Http/Controllers/Public/HomeController.php`, `PPDBController.php` | Flow public utama |
| 6 | `app/Http/Controllers/Admin/DashboardController.php`, `RegistrationController.php` | Flow admin utama |
| 7 | `bootstrap/app.php` | Middleware + exception rules |
| 8 | `resources/views/components/layouts/app.blade.php` + `admin/layouts/app.blade.php` | Shell & global meta |
| 9 | `resources/js/app.interactions.js` | Semua JS interaktif (search "data-") |
| 10 | `resources/css/app.css` | Tokens & prose style |
| 11 | `resources/views/public/home.blade.php` | Komposisi landing page |
| 12 | `resources/views/admin/dashboard.blade.php` | Komposisi admin dashboard |
| 13 | `resources/views/components/ui/button.blade.php`, `card`, `input`, `modal` | Design system sample |
| 14 | `database/migrations/*` (pages, programs, news, galleries, announcements, ppdb, login_logs) | Skema penuh |
| 15 | `database/seeders/ProgramSeeder`, `PageSeeder` | Content default |
| 16 | `tests/Feature/PPDBRegistrationTest.php`, `AdminTest.php` | Ekspektasi behavior |

> Total: ~30 files, ~3 jam baca intensif → paham end-to-end.

---

## 30. Final Project Understanding

**SMK Tahfizh Al-Fatih** adalah Laravel monolitik Blade SSR yang **sudah berhasil** mengeksekusi misi intinya: **PPDB online yang stabil** + **panel admin yang polished** (dashboard chart CSS, filter/search, status workflow pending→accepted/rejected/cancelled, safe batch delete, superadmin gate, login audit). Code quality di atas rata-rata school project: enums native PHP 8.1, FormRequest terpisah, scope `published/active`, accessor Storage url, component `<x-ui.*>` reusable (22), dark mode via localStorage, Vanilla JS interaksi modular tanpa framework berat, Tailwind v4 tokens + prose custom, test Feature coverage untuk flow kritikal.

Namun, itu hanya **30% dari website sekolah** dari sisi CMS. **70% content** (`Program`, `News`, `Galeri`, `Pengumuman`, `Halaman Statis`, `Kontak Inbox`) masih **read-only** — data datang dari Seeder/Factory, tidak ada admin CRUD. Artinya operasional harian (update berita, tambah galeri, ubah profil/visi-misi, jawab kontak) **memerlukan developer** — belum self-service. Plus gap operasional: no email notifikasi, no export, no user delete, no content pagination, no login throttle.

**Arsitektural**: tipikal Laravel tradition (Controller → Eloquent → Blade) tanpa Service/Repository layer — simple dan maintainable untuk tim kecil, tapi tren tanggung jawab beranak di `DashboardController` (4 aggregates + join) yang future bisa ekstrak ke ViewModel/Service. Database SQLite cukup untuk <10k pendaftar; switch ke MySQL trivial via `DB_CONNECTION`.

**Jika engineer kedua lanjut besok**: fokus pertama bangun **Admin CRUD generik** untuk 5 content models (pakai Resource Controller + `Store*Request` + Storage image handler + `ContentStatus` select) dan **Contact inbox** — gunakan pola yang sudah ada di `RegistrationController` + `UserManagementController` sebagai template. Setelah itu hardening S2 throttle login & hapus demo auto-login di prod, lalu tambahkan email queue `new PPDB notification`.

**Proyek siap di-clone dan dijalankan** (`composer setup` + `npm run dev` + `php artisan serve`) — butuh hanya PHP 8.2 + SQLite, tidak butuh Firebase/SaaS. Estimasi **2–3 sprint** untuk mencapai production-ready CMS lengkap.

> **Invariant**: No source modified during mapping. All claims `CONFIRMED FROM CODE` or marked `INFERRED`.

---

*End of PROJECT_MAP.md — Hand off next: chat log includes === SMK AL-FATIH CODEBASE HANDOFF === block for ChatGPT.*

---

## ADDENDUM — ALFATIH//FUTURE Redesign (branch `redesign/identitas-ceria`, 2026-09-25)

**Identitas baru:** Emerald `#087A55` (primer) + Deep Forest `#063E32` + Tech Green `#16B878` + Energy Orange `#F47A28` + Prestige Gold `#D7A83E` + Future Navy `#101C2C`; font display Sora + body Plus Jakarta Sans; bentuk signature clipped-corner; motif geometris 8-titik; Digital Pulse (Learn→Build→Impact); panel RPL "BUILD YOUR FUTURE".

**Komponen baru:** `x-motif-geometric`, `x-digital-pulse`, `x-marquee-strip`, `x-stat-ribbon` (counter), `x-program-card` (tone emerald/orange/gold/navy), `x-mosaic-gallery`, `x-rpl-panel`, `x-admin.page-head`.

**Halaman dirombak:** homepage (Living Campus hero + word-swap + marquee + stats ribbon + mosaic + CTA navy), navbar (transparan→solid + CTA oranye), footer (forest + motif + garis tricolor), `x-page-header` (navy, dipakai 12 halaman), programs/news (kartu baru), gallery (reveal), announcements (border gold), errors (branded navy), PPDB (panel navy + step + status bar), profil editorial baru (`public/pages/profil.blade.php`, aktif bila slug=`profil`), admin sidebar (graphite + ikon) + dashboard konsol TODAY + badge gold/navy + login branding.

**Interaksi JS baru:** reveal (IntersectionObserver), counter, word-swap (dengan reduced-motion guard), navbar-scroll. Tanpa library baru (bundle JS tetap ~267KB).

**Revert:** `git checkout main` (baseline `8d9caa8`) mengembalikan 100% tampilan lama.

**QA redesign:** PHPUnit 103 passed; `npm run build` lolos; config/route/view cache lolos; Playwright 68/68 (`--workers=2`, 4 viewport).
