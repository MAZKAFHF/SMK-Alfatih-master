<?php

namespace App\Console\Commands;

use App\Models\Announcement;
use App\Models\ApplicationDocument;
use App\Models\AuditLog;
use App\Models\EmailLog;
use App\Models\InterviewSlot;
use App\Models\PPDBRegistration;
use App\Models\PpdbPeriod;
use App\Models\User;
use App\Services\PpdbContext;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CleanupE2eData extends Command
{
    protected $signature = 'app:cleanup-e2e {--force : Permanently remove only records carrying explicit E2E markers}';

    protected $description = 'Preview or remove Playwright/E2E fixture data without touching normal operational records';

    public function handle(): int
    {
        $periodIds = PpdbPeriod::query()->where('academic_year', 'like', 'E2E-%')->pluck('id');
        $userIds = User::query()->where(function ($query) {
            $query->where('email', 'like', '%@example.id')
                ->orWhere('email', 'like', '%@window.test');
        })->pluck('id');
        $announcementIds = Announcement::withTrashed()->where('title', 'like', 'E2E-ANN-%')->pluck('id');
        $applicationIds = PPDBRegistration::withTrashed()
            ->where(function ($query) use ($periodIds, $userIds) {
                $query->whereIn('period_id', $periodIds)
                    ->orWhereIn('applicant_account_id', $userIds);
            })->pluck('id');

        $counts = [
            ['Periode E2E', $periodIds->count()],
            ['Akun domain uji', $userIds->count()],
            ['Aplikasi terkait', $applicationIds->count()],
            ['Pengumuman E2E', $announcementIds->count()],
        ];
        $this->table(['Target', 'Jumlah'], $counts);

        if (! $this->option('force')) {
            $this->info('Preview saja. Jalankan kembali dengan --force setelah backup untuk menghapus target di atas.');

            return self::SUCCESS;
        }

        if ($periodIds->isEmpty() && $userIds->isEmpty() && $announcementIds->isEmpty() && $applicationIds->isEmpty()) {
            $this->info('Tidak ada data E2E yang perlu dibersihkan.');

            return self::SUCCESS;
        }

        $files = ApplicationDocument::query()
            ->whereIn('application_id', $applicationIds)
            ->with('revisions:id,document_id,path')
            ->get()
            ->flatMap(function (ApplicationDocument $document) {
                $disk = $document->disk ?: 'ppdb_private';
                $items = collect($document->path ? [[$disk, $document->path]] : []);

                return $items->concat(
                    $document->revisions->map(fn ($revision) => [$disk, $revision->path])
                );
            })
            ->filter(fn ($item) => filled($item[1] ?? null))
            ->unique(fn ($item) => $item[0].'|'.$item[1])
            ->values();

        $photoFiles = PPDBRegistration::withTrashed()->whereIn('id', $applicationIds)
            ->whereNotNull('photo_path')->pluck('photo_path')
            ->map(fn ($path) => ['public', $path]);
        $files = $files->concat($photoFiles)->unique(fn ($item) => $item[0].'|'.$item[1]);

        DB::transaction(function () use ($periodIds, $userIds, $announcementIds, $applicationIds): void {
            EmailLog::query()->whereIn('application_id', $applicationIds)
                ->orWhere(function ($query) {
                    $query->where('recipient', 'like', '%@example.id')
                        ->orWhere('recipient', 'like', '%@window.test');
                })->delete();

            AuditLog::query()->whereIn('user_id', $userIds)
                ->orWhere(function ($query) use ($applicationIds) {
                    $query->where('auditable_type', PPDBRegistration::class)
                        ->whereIn('auditable_id', $applicationIds);
                })
                ->orWhere(function ($query) use ($periodIds) {
                    $query->where('auditable_type', PpdbPeriod::class)
                        ->whereIn('auditable_id', $periodIds);
                })
                ->orWhere(function ($query) use ($announcementIds) {
                    $query->where('auditable_type', Announcement::class)
                        ->whereIn('auditable_id', $announcementIds);
                })->delete();

            PPDBRegistration::withTrashed()->whereIn('id', $applicationIds)->forceDelete();
            InterviewSlot::query()->whereIn('period_id', $periodIds)->delete();
            Announcement::withTrashed()->whereIn('id', $announcementIds)->forceDelete();
            User::query()->whereIn('id', $userIds)->delete();
            PpdbPeriod::query()->whereIn('id', $periodIds)->delete();
        });

        $deletedFiles = 0;
        foreach ($files as [$disk, $path]) {
            if (Storage::disk($disk)->exists($path) && Storage::disk($disk)->delete($path)) {
                $deletedFiles++;
            }
        }

        PpdbContext::flush();
        $this->info('Cleanup E2E selesai. File terkait dihapus: '.$deletedFiles.'.');

        return self::SUCCESS;
    }
}
