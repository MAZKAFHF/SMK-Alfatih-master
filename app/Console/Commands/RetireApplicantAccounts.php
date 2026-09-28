<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\ApplicantAccountLifecycleService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class RetireApplicantAccounts extends Command
{
    protected $signature = 'app:applicants:retire {--dry-run} {--account=} {--batch=}';

    protected $description = 'Retire eligible applicant accounts while preserving PPDB history';

    public function handle(ApplicantAccountLifecycleService $service): int
    {
        $dryRun = (bool) $this->option('dry-run');
        if (! config('retention.applicants.enabled') && ! $dryRun) {
            $this->error('Applicant retirement remains disabled until the school approves its retention policy.');

            return self::FAILURE;
        }

        $cutoff = CarbonImmutable::now('UTC');
        $batch = max(10, (int) ($this->option('batch') ?: config('retention.applicants.batch_size')));
        $query = User::where('is_applicant', true)->where('is_admin', false)->where('is_superadmin', false);
        if ($this->option('account')) {
            $query->whereKey((int) $this->option('account'));
        }

        $summary = ['checked' => 0, 'eligible' => 0, 'retired' => 0, 'blocked' => 0, 'failed' => 0];
        $skipReasons = [];
        $query->orderBy('id')->chunkById($batch, function ($users) use ($service, $cutoff, $dryRun, &$summary, &$skipReasons): void {
            foreach ($users as $user) {
                $summary['checked']++;
                $evaluation = $service->evaluate($user, $cutoff);
                if (! $evaluation['eligible']) {
                    $summary['blocked']++;
                    foreach ($evaluation['reasons'] as $reason) {
                        $skipReasons[$reason] = ($skipReasons[$reason] ?? 0) + 1;
                    }
                    $this->line("#{$user->id} blocked: ".implode(', ', $evaluation['reasons']));

                    continue;
                }
                $summary['eligible']++;
                if ($dryRun) {
                    $this->line("#{$user->id} eligible; applications={$evaluation['application_count']}; due={$evaluation['due_at']}");

                    continue;
                }
                try {
                    $result = $service->retire($user->id, $cutoff);
                    $summary[$result['status'] === 'retired' ? 'retired' : 'blocked']++;
                } catch (\Throwable $e) {
                    $summary['failed']++;
                    report($e);
                    $this->warn("Failed account #{$user->id}: {$e->getMessage()}");
                }
            }
        });

        $this->info(($dryRun ? 'Dry run' : 'Retirement').' complete: '.json_encode($summary));
        if ($skipReasons !== []) {
            $this->line('Skipped reasons: '.json_encode($skipReasons));
        }
        if ($dryRun) {
            $this->line("Accounts checked: {$summary['checked']}; Eligible: {$summary['eligible']}; Would retire: {$summary['eligible']}; Skipped: {$summary['blocked']}");
        }

        return $summary['failed'] > 0 ? self::FAILURE : self::SUCCESS;
    }
}
