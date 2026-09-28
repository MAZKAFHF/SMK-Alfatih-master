<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\LoginLog;
use App\Services\AuditService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PruneDatabaseLogs extends Command
{
    protected $signature = 'app:retention:logs {--dry-run} {--days=} {--batch=}';

    protected $description = 'Prune audit and login logs older than the configured retention period';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        if (! config('retention.logs.enabled') && ! $dryRun) {
            $this->error('Database log retention is disabled.');

            return self::FAILURE;
        }

        $days = max(1, (int) ($this->option('days') ?: config('retention.logs.days')));
        $batch = max(100, (int) ($this->option('batch') ?: config('retention.logs.batch_size')));
        $cutoff = CarbonImmutable::now('UTC')->subDays($days);
        $counts = [
            'audit_logs' => AuditLog::where('created_at', '<=', $cutoff)->count(),
            'login_logs' => LoginLog::where('created_at', '<=', $cutoff)->count(),
        ];

        $this->table(['Table', 'Eligible'], collect($counts)->map(fn ($count, $table) => [$table, $count])->values()->all());
        $this->line('Cutoff UTC: '.$cutoff->toIso8601String());
        if ($dryRun) {
            $this->info('Dry run complete; no data changed.');

            return self::SUCCESS;
        }

        $deleted = [];
        $failed = [];
        foreach ([LoginLog::class, AuditLog::class] as $model) {
            $table = (new $model)->getTable();
            try {
                $total = 0;
                do {
                    $ids = $model::where('created_at', '<=', $cutoff)->orderBy('id')->limit($batch)->pluck('id');
                    $count = $ids->isEmpty() ? 0 : DB::table($table)->whereIn('id', $ids)->delete();
                    $total += $count;
                } while ($count === $batch);
                $deleted[$table] = $total;
            } catch (\Throwable $e) {
                $failed[$table] = $e->getMessage();
                report($e);
                $this->warn("Failed {$table}: {$e->getMessage()}");
            }
        }

        AuditService::system('database_logs_pruned', null, [
            'cutoff' => $cutoff->toIso8601String(),
            'deleted' => $deleted,
            'failed_tables' => array_keys($failed),
        ]);
        $this->info('Database log retention completed.');

        return $failed === [] ? self::SUCCESS : self::FAILURE;
    }
}
