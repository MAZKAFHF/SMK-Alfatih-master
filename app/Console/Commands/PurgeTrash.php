<?php

namespace App\Console\Commands;

use App\Services\AuditService;
use App\Services\TrashPurgeService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class PurgeTrash extends Command
{
    protected $signature = 'app:trash:purge {--dry-run} {--days=} {--batch=}';

    protected $description = 'Permanently delete eligible safe trash items';

    public function handle(TrashPurgeService $service): int
    {
        $dryRun = (bool) $this->option('dry-run');
        if (! config('retention.trash.enabled') && ! $dryRun) {
            $this->error('Trash auto-purge is disabled.');

            return self::FAILURE;
        }

        $days = max(1, (int) ($this->option('days') ?: config('retention.trash.days')));
        $batch = max(10, (int) ($this->option('batch') ?: config('retention.trash.batch_size')));
        $cutoff = CarbonImmutable::now('UTC')->subDays($days);
        $rows = [];
        foreach ($service->models() as $name => $model) {
            $rows[] = [$name, $service->eligibleCount($model, $cutoff)];
        }
        $rows[] = ['programs', 'EXCLUDED: protects PPDB history'];
        $rows[] = ['ppdb_registrations', 'EXCLUDED: protects PPDB history/files'];
        $this->table(['Model', 'Eligible'], $rows);
        $this->line('Cutoff UTC: '.$cutoff->toIso8601String());
        if ($dryRun) {
            $this->info('Dry run complete; no data or files changed.');

            return self::SUCCESS;
        }

        $summary = ['purged' => 0, 'skipped' => 0, 'failed' => 0];
        foreach ($service->models() as $model) {
            $model::onlyTrashed()->where('deleted_at', '<=', $cutoff)->orderBy('id')->chunkById($batch, function ($items) use ($service, $model, $cutoff, &$summary): void {
                foreach ($items as $item) {
                    try {
                        $result = $service->purgeOne($model, $item->id, $cutoff);
                        $summary[$result['status']]++;
                    } catch (\Throwable $e) {
                        $summary['failed']++;
                        report($e);
                        $this->warn("Failed {$model} #{$item->id}: {$e->getMessage()}");
                    }
                }
            });
        }

        AuditService::system('trash_auto_purge_completed', null, ['cutoff' => $cutoff->toIso8601String(), 'summary' => $summary]);
        $this->info('Trash purge completed: '.json_encode($summary));

        return $summary['failed'] > 0 ? self::FAILURE : self::SUCCESS;
    }
}
