<?php

use App\Http\Controllers\Admin\AnnouncementController as AdminAnnouncementController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ForgotPasswordController;
use App\Http\Controllers\Admin\GalleryController as AdminGalleryController;
use App\Http\Controllers\Admin\LoginLogController;
use App\Http\Controllers\Admin\NewsController as AdminNewsController;
use App\Http\Controllers\Admin\PageController as AdminPageController;
use App\Http\Controllers\Admin\ProgramController as AdminProgramController;
use App\Http\Controllers\Admin\RegistrationController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TrashController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\Public\AnnouncementController;
use App\Http\Controllers\Public\ContactController;
use App\Http\Controllers\Public\GalleryController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\NewsController;
use App\Http\Controllers\Public\PageController;
use App\Http\Controllers\Public\PPDBController;
use App\Http\Controllers\Public\ProgramController;
use App\Http\Controllers\Public\SitemapController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/program-keahlian', [ProgramController::class, 'index'])->name('programs.index');
Route::get('/program-keahlian/{program:slug}', [ProgramController::class, 'show'])->name('programs.show');

Route::get('/berita', [NewsController::class, 'index'])->name('news.index');
Route::get('/berita/{news:slug}', [NewsController::class, 'show'])->name('news.show');

Route::get('/galeri', [GalleryController::class, 'index'])->name('gallery.index');

Route::get('/pengumuman', [AnnouncementController::class, 'index'])->name('announcements.index');

Route::get('/kontak', [ContactController::class, 'index'])->name('contact.index');
Route::post('/kontak', [ContactController::class, 'send'])->middleware('throttle:5,1')->name('contact.send');

Route::get('/ppdb', [PPDBController::class, 'index'])->name('ppdb.index');
// URL publik lama tetap ramah bagi tautan yang pernah dibagikan, tetapi tidak
// lagi menjalankan formulir/lookup berbasis nomor pendaftaran.
Route::get('/ppdb/siswa', fn () => redirect()->route('portal.register', [], 301));
Route::post('/ppdb', fn () => redirect()->route('portal.register', [], 303));
Route::get('/ppdb/status', fn () => redirect()->route('portal.login', [], 301));

// ALFATIH//FUTURE — Portal Pemohon (akun orang tua, banyak siswa per akun)
Route::prefix('portal')->name('portal.')->group(function () {
    // Halaman auth portal SENGAJA tanpa middleware `guest`: middleware `guest`
    // global melempar SEMUA pengguna login (termasuk admin) ke /admin.dashboard,
    // sehingga admin yang klik "Buat Akun" tidak pernah sampai ke portal.
    // Penanganan "sudah login" dilakukan di controller (pemohon -> dashboard,
    // admin -> tetap lihat form + notifikasi).
    Route::get('/daftar', [\App\Http\Controllers\Portal\AuthController::class, 'showRegister'])->name('register');
    Route::post('/daftar', [\App\Http\Controllers\Portal\AuthController::class, 'register'])->middleware('throttle:5,1')->name('register.store');
    Route::get('/masuk', [\App\Http\Controllers\Portal\AuthController::class, 'showLogin'])->name('login');
    Route::post('/masuk', [\App\Http\Controllers\Portal\AuthController::class, 'login'])->middleware('throttle:6,1')->name('login.store');
    Route::get('/lupa-password', [\App\Http\Controllers\Portal\AuthController::class, 'showForgot'])->name('password.request');
    Route::post('/lupa-password', [\App\Http\Controllers\Portal\AuthController::class, 'sendReset'])->middleware('throttle:5,1')->name('password.email');
    Route::get('/reset-password/{token}', [\App\Http\Controllers\Portal\AuthController::class, 'showReset'])->name('password.reset');
    Route::post('/reset-password', [\App\Http\Controllers\Portal\AuthController::class, 'reset'])->name('password.update');
    Route::get('/verifikasi/{id}/{hash}', [\App\Http\Controllers\Portal\AuthController::class, 'verifyEmail'])->name('verification.verify');

    Route::middleware(['auth', 'applicant'])->group(function () {
        Route::post('/keluar', [\App\Http\Controllers\Portal\AuthController::class, 'logout'])->name('logout');
        Route::post('/verifikasi/kirim-ulang', [\App\Http\Controllers\Portal\AuthController::class, 'resendVerification'])->middleware('throttle:3,1')->name('verification.resend');
        Route::get('/', [\App\Http\Controllers\Portal\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/notifikasi', [\App\Http\Controllers\Portal\DashboardController::class, 'notifications'])->name('notifications');
        Route::post('/notifikasi/{id}/baca', [\App\Http\Controllers\Portal\DashboardController::class, 'readNotification'])->name('notifications.read');

        Route::get('/aplikasi/baru', [\App\Http\Controllers\Portal\ApplicationController::class, 'create'])->name('applications.create');
        Route::post('/aplikasi', [\App\Http\Controllers\Portal\ApplicationController::class, 'store'])->middleware('throttle:10,1')->name('applications.store');
        Route::get('/aplikasi/{application}', [\App\Http\Controllers\Portal\ApplicationController::class, 'show'])->name('applications.show');
        Route::get('/aplikasi/{application}/ubah', [\App\Http\Controllers\Portal\ApplicationController::class, 'edit'])->name('applications.edit');
        Route::put('/aplikasi/{application}', [\App\Http\Controllers\Portal\ApplicationController::class, 'update'])->name('applications.update');
        Route::get('/aplikasi/{application}/review', [\App\Http\Controllers\Portal\ApplicationController::class, 'review'])->name('applications.review');
        Route::post('/aplikasi/{application}/kirim', [\App\Http\Controllers\Portal\ApplicationController::class, 'submit'])->name('applications.submit');
        Route::post('/aplikasi/{application}/dokumen/{type}', [\App\Http\Controllers\Portal\DocumentController::class, 'upload'])->name('documents.upload');
        Route::get('/dokumen/{document}/pratinjau', [\App\Http\Controllers\Portal\DocumentController::class, 'preview'])->name('documents.preview');

        Route::get('/aplikasi/{application}/slot', [\App\Http\Controllers\Portal\InterviewController::class, 'slots'])->name('slots.index');
        Route::post('/aplikasi/{application}/slot', [\App\Http\Controllers\Portal\InterviewController::class, 'book'])->name('slots.book');
        Route::post('/aplikasi/{application}/reschedule', [\App\Http\Controllers\Portal\InterviewController::class, 'requestReschedule'])->name('reschedule.store');
    });
});

Route::get('/health', [HealthController::class, 'index'])->name('health');
Route::get('/up', fn () => response()->json(['status' => 'ok', 'time' => now()->toIso8601String()]))->name('up.simple');

// SEO
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', function () {
    $isProduction = app()->environment('production');
    $content = $isProduction
        ? "User-agent: *\nAllow: /\nSitemap: ".url('/sitemap.xml')."\n"
        : "User-agent: *\nDisallow: /\n";

    return response($content, 200)->header('Content-Type', 'text/plain');
})->name('robots');

// Temporary, token-protected production bootstrap endpoint. Remove this route
// immediately after the first successful initialization because migrate:fresh
// permanently deletes every table before recreating and seeding the database.
Route::get('/init-db', function (Request $request) {
    $expectedToken = (string) config('app.init_db_token');
    $providedToken = (string) $request->query('token');

    abort_if($expectedToken === '', 503, 'INIT_DB_TOKEN belum dikonfigurasi.');
    abort_unless(hash_equals($expectedToken, $providedToken), 403, 'Token init database tidak valid.');

    Artisan::call('config:clear');
    // The production default is a database cache. During first boot its table
    // may not exist yet, so clear through an in-memory store before rebuilding.
    config(['cache.default' => 'array']);
    Artisan::call('cache:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    Artisan::call('migrate:fresh', [
        '--seed' => true,
        '--force' => true,
    ]);

    return response(
        "SUKSES: cache Laravel telah dibersihkan dan database berhasil dibuat ulang beserta data seed.\n",
        200,
        ['Content-Type' => 'text/plain; charset=UTF-8'],
    );
})->withoutMiddleware([
    \Illuminate\Session\Middleware\StartSession::class,
    \Illuminate\View\Middleware\ShareErrorsFromSession::class,
])->name('system.init-db');

Route::get('/{slug}', [PageController::class, 'show'])
    ->where('slug', '(?!admin)[a-z0-9-]+')
    ->name('pages.show');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
        Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
        Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
        Route::get('/reset-password/{token}', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
        Route::post('/reset-password', [ForgotPasswordController::class, 'reset'])->name('password.update');
    });

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/work-queue', [\App\Http\Controllers\Admin\WorkQueueController::class, 'index'])->name('work-queue.index');

        // Registrations with trash
        Route::get('/registrations/trash', [RegistrationController::class, 'trash'])->name('registrations.trash');
        Route::post('/registrations/{id}/restore', [RegistrationController::class, 'restore'])->name('registrations.restore');
        Route::delete('/registrations/{id}/force', [RegistrationController::class, 'forceDelete'])->middleware('superadmin')->name('registrations.force-delete');
        Route::get('/registrations', [RegistrationController::class, 'index'])->name('registrations.index');
        Route::get('/registrations/create', [RegistrationController::class, 'create'])->name('registrations.create');
        Route::post('/registrations', [RegistrationController::class, 'store'])->name('registrations.store');
        Route::get('/registrations/export', [RegistrationController::class, 'export'])->name('registrations.export');
        Route::get('/registrations/cetak-daftar', [RegistrationController::class, 'printList'])->name('registrations.print-list');
        Route::get('/registrations/{registration}', [RegistrationController::class, 'show'])->name('registrations.show');
        Route::delete('/registrations/{registration}', [RegistrationController::class, 'destroy'])->name('registrations.destroy');
        Route::get('/registrations/{registration}/cetak', [RegistrationController::class, 'print'])->name('registrations.print');

        // ALFATIH//CONTROL — PPDB workflow (verifikasi, dokumen, keputusan, slot)
        Route::post('/registrations/{registration}/verify', [\App\Http\Controllers\Admin\PpdbWorkflowController::class, 'verify'])->name('registrations.verify');
        Route::post('/registrations/{registration}/request-revision', [\App\Http\Controllers\Admin\PpdbWorkflowController::class, 'requestRevision'])->name('registrations.revise');
        Route::post('/registrations/{registration}/decide', [\App\Http\Controllers\Admin\PpdbWorkflowController::class, 'decide'])->name('registrations.decide');
        Route::post('/registrations/{registration}/release', [\App\Http\Controllers\Admin\PpdbWorkflowController::class, 'release'])->name('registrations.release');
        Route::post('/registrations/{registration}/notes', [\App\Http\Controllers\Admin\PpdbWorkflowController::class, 'addNote'])->name('registrations.note');
        Route::post('/registrations/{registration}/resend-email', [\App\Http\Controllers\Admin\PpdbWorkflowController::class, 'resendEmail'])->name('registrations.resend');
        Route::post('/documents/{document}/review', [\App\Http\Controllers\Admin\PpdbWorkflowController::class, 'reviewDocument'])->name('documents.review');
        Route::get('/documents/{document}/preview', [\App\Http\Controllers\Portal\DocumentController::class, 'preview'])->name('documents.preview');

        Route::get('/interview-slots', [\App\Http\Controllers\Admin\InterviewSlotController::class, 'index'])->name('slots.index');
        Route::post('/interview-slots', [\App\Http\Controllers\Admin\InterviewSlotController::class, 'store'])->name('slots.store');
        Route::post('/interview-slots/{slot}/toggle', [\App\Http\Controllers\Admin\InterviewSlotController::class, 'toggle'])->name('slots.toggle');
        Route::delete('/interview-slots/{slot}', [\App\Http\Controllers\Admin\InterviewSlotController::class, 'destroy'])->name('slots.destroy');
        Route::post('/appointments/{appointment}/complete', [\App\Http\Controllers\Admin\InterviewSlotController::class, 'complete'])->name('appointments.complete');
        Route::post('/reschedules/{reschedule}/decide', [\App\Http\Controllers\Admin\InterviewSlotController::class, 'decideReschedule'])->name('reschedules.decide');

        // Periode PPDB (manajemen + histori)
        Route::get('/periods', [\App\Http\Controllers\Admin\PeriodController::class, 'index'])->name('periods.index');
        Route::get('/periods/create', [\App\Http\Controllers\Admin\PeriodController::class, 'create'])->name('periods.create');
        Route::post('/periods', [\App\Http\Controllers\Admin\PeriodController::class, 'store'])->name('periods.store');
        Route::get('/periods/{period}/edit', [\App\Http\Controllers\Admin\PeriodController::class, 'edit'])->name('periods.edit');
        Route::put('/periods/{period}', [\App\Http\Controllers\Admin\PeriodController::class, 'update'])->name('periods.update');
        Route::post('/periods/{period}/open', [\App\Http\Controllers\Admin\PeriodController::class, 'open'])->name('periods.open');
        Route::post('/periods/{period}/close', [\App\Http\Controllers\Admin\PeriodController::class, 'close'])->name('periods.close');
        Route::post('/periods/{period}/reopen', [\App\Http\Controllers\Admin\PeriodController::class, 'reopen'])->middleware('superadmin')->name('periods.reopen');
        Route::post('/periods/{period}/archive', [\App\Http\Controllers\Admin\PeriodController::class, 'archive'])->name('periods.archive');
        Route::post('/periods/{period}/complete', [\App\Http\Controllers\Admin\PeriodController::class, 'complete'])->middleware('superadmin')->name('periods.complete');
        Route::delete('/periods/{period}', [\App\Http\Controllers\Admin\PeriodController::class, 'destroy'])->middleware('superadmin')->name('periods.destroy');

        // CMS
        Route::resource('programs', AdminProgramController::class)->except(['show']);
        Route::get('programs/trash', [AdminProgramController::class, 'trash'])->name('programs.trash');
        Route::post('programs/{id}/restore', [AdminProgramController::class, 'restore'])->name('programs.restore');
        Route::delete('programs/{id}/force', [AdminProgramController::class, 'forceDelete'])->middleware('superadmin')->name('programs.force-delete');

        Route::resource('news', AdminNewsController::class)->except(['show']);
        Route::get('news/trash', [AdminNewsController::class, 'trash'])->name('news.trash');
        Route::post('news/{id}/restore', [AdminNewsController::class, 'restore'])->name('news.restore');
        Route::delete('news/{id}/force', [AdminNewsController::class, 'forceDelete'])->middleware('superadmin')->name('news.force-delete');

        Route::resource('galleries', AdminGalleryController::class)->except(['show']);
        Route::get('galleries/trash', [AdminGalleryController::class, 'trash'])->name('galleries.trash');
        Route::post('galleries/{id}/restore', [AdminGalleryController::class, 'restore'])->name('galleries.restore');
        Route::delete('galleries/{id}/force', [AdminGalleryController::class, 'forceDelete'])->middleware('superadmin')->name('galleries.force-delete');

        Route::resource('announcements', AdminAnnouncementController::class)->except(['show']);
        Route::get('announcements/trash', [AdminAnnouncementController::class, 'trash'])->name('announcements.trash');
        Route::post('announcements/{id}/restore', [AdminAnnouncementController::class, 'restore'])->name('announcements.restore');
        Route::delete('announcements/{id}/force', [AdminAnnouncementController::class, 'forceDelete'])->middleware('superadmin')->name('announcements.force-delete');

        Route::resource('pages', AdminPageController::class)->except(['show']);
        Route::get('pages/trash', [AdminPageController::class, 'trash'])->name('pages.trash');
        Route::post('pages/{id}/restore', [AdminPageController::class, 'restore'])->name('pages.restore');
        Route::delete('pages/{id}/force', [AdminPageController::class, 'forceDelete'])->middleware('superadmin')->name('pages.force-delete');

        // Contact inbox
        Route::get('contact-messages/trash', [ContactMessageController::class, 'trash'])->name('contact-messages.trash');
        Route::post('contact-messages/{id}/restore', [ContactMessageController::class, 'restore'])->name('contact-messages.restore');
        Route::delete('contact-messages/{id}/force', [ContactMessageController::class, 'forceDelete'])->middleware('superadmin')->name('contact-messages.force-delete');
        Route::get('contact-messages', [ContactMessageController::class, 'index'])->name('contact-messages.index');
        Route::get('contact-messages/{contactMessage}', [ContactMessageController::class, 'show'])->name('contact-messages.show');
        Route::post('contact-messages/{contactMessage}/read', [ContactMessageController::class, 'markRead'])->name('contact-messages.read');
        Route::post('contact-messages/{contactMessage}/unread', [ContactMessageController::class, 'markUnread'])->name('contact-messages.unread');
        Route::post('contact-messages/{contactMessage}/archive', [ContactMessageController::class, 'archive'])->name('contact-messages.archive');
        Route::post('contact-messages/{contactMessage}/unarchive', [ContactMessageController::class, 'unarchive'])->name('contact-messages.unarchive');
        Route::put('contact-messages/{contactMessage}/handling', [ContactMessageController::class, 'updateHandling'])->name('contact-messages.handling');
        Route::delete('contact-messages/{contactMessage}', [ContactMessageController::class, 'destroy'])->name('contact-messages.destroy');

        // Settings
        Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');

        // Trash overview + Audit
        Route::get('trash', [TrashController::class, 'index'])->name('trash.index');
        Route::get('audit-logs', [AuditLogController::class, 'index'])->middleware('superadmin')->name('audit-logs.index');

        Route::middleware('superadmin')->group(function () {
            Route::get('/login-logs', [LoginLogController::class, 'index'])->name('login-logs.index');

            Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
            Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
            Route::put('/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
            Route::post('/users/{user}/toggle-active', [UserManagementController::class, 'toggleActive'])->name('users.toggle-active');
            Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');
        });
    });
});
