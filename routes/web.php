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
Route::get('/ppdb/siswa', [PPDBController::class, 'siswa'])->name('ppdb.siswa');
Route::post('/ppdb', [PPDBController::class, 'store'])->middleware('throttle:5,1')->name('ppdb.store');
Route::get('/ppdb/status', [PPDBController::class, 'status'])->name('ppdb.status');

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

        // Registrations with trash
        Route::get('/registrations/trash', [RegistrationController::class, 'trash'])->name('registrations.trash');
        Route::post('/registrations/{id}/restore', [RegistrationController::class, 'restore'])->name('registrations.restore');
        Route::delete('/registrations/{id}/force', [RegistrationController::class, 'forceDelete'])->middleware('superadmin')->name('registrations.force-delete');
        Route::get('/registrations', [RegistrationController::class, 'index'])->name('registrations.index');
        Route::delete('/registrations', [RegistrationController::class, 'destroyAll'])->name('registrations.destroy-all');
        Route::get('/registrations/export', [RegistrationController::class, 'export'])->name('registrations.export');
        Route::get('/registrations/{registration}', [RegistrationController::class, 'show'])->name('registrations.show');
        Route::put('/registrations/{registration}', [RegistrationController::class, 'update'])->name('registrations.update');
        Route::delete('/registrations/{registration}', [RegistrationController::class, 'destroy'])->name('registrations.destroy');

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
        Route::delete('contact-messages/{contactMessage}', [ContactMessageController::class, 'destroy'])->name('contact-messages.destroy');

        // Settings
        Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
        Route::put('settings/ppdb', [SettingController::class, 'ppdbUpdate'])->name('settings.ppdb');

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
            Route::get('/users/{user}/reset-link', [UserManagementController::class, 'resetLink'])->name('users.reset-link');
        });
    });
});
