<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Models\PPDBRegistration;
use App\Models\PpdbPeriod;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

class ApplicantAccountLifecycleService
{
    public const TYPE_UNVERIFIED_ORPHAN = 'unverified_orphan';

    public const TYPE_VERIFIED_UNUSED = 'verified_unused';

    public const TYPE_REAL_APPLICANT = 'real_applicant';

    /** @return array{eligible:bool,type:string,reasons:list<string>,due_at:?string,application_count:int,period_ids:list<int>} */
    public function evaluate(User $user, CarbonInterface $cutoff): array
    {
        $user->loadMissing('applicationsWithTrashed.period');
        $applications = $user->applicationsWithTrashed;
        $periodIds = $applications->pluck('period_id')->filter()->unique()->values()->map(fn ($id) => (int) $id)->all();
        $reasons = [];

        if (! $user->is_applicant || $user->is_admin || $user->is_superadmin) {
            $reasons[] = 'protected_role';
        }

        if ($applications->isEmpty()) {
            return $user->email_verified_at === null
                ? $this->evaluateUnverifiedOrphan($user, $cutoff, $reasons)
                : $this->evaluateVerifiedUnused($user, $cutoff, $reasons);
        }

        $dueAt = null;
        foreach ($applications as $application) {
            $period = $application->period;
            if (! $period) {
                $reasons[] = 'missing_period';

                continue;
            }
            if (! $period->isAdmissionsCompleted()) {
                $reasons[] = 'ppdb_not_completed';
            }
            // Explicit per-period deadline wins; otherwise fall back to
            // operational completion + configurable grace (school-approvable).
            $retentionUntil = $period->account_retention_until
                ?? $period->operational_completed_at?->copy()->addDays((int) config('retention.applicants.real_retention_days', 90));
            if (! $retentionUntil) {
                $reasons[] = 'retention_not_configured';
            } else {
                $dueAt = ! $dueAt || $retentionUntil->gt($dueAt)
                    ? $retentionUntil
                    : $dueAt;
                if ($retentionUntil->gt($cutoff)) {
                    $reasons[] = 'retention_not_expired';
                }
            }
            if (! in_array($application->application_status, [
                ApplicationStatus::Passed,
                ApplicationStatus::NotPassed,
                ApplicationStatus::Cancelled,
                ApplicationStatus::Draft,
            ], true)) {
                $reasons[] = 'application_not_terminal';
            }
        }

        $applicationIds = $applications->pluck('id');
        if (DB::table('change_requests')->whereIn('application_id', $applicationIds)->where('status', 'pending')->exists()) {
            $reasons[] = 'pending_correction';
        }
        if (DB::table('reschedule_requests')
            ->join('interview_appointments', 'interview_appointments.id', '=', 'reschedule_requests.appointment_id')
            ->whereIn('interview_appointments.application_id', $applicationIds)
            ->where('reschedule_requests.status', 'pending')->exists()) {
            $reasons[] = 'pending_interview';
        }
        // An unreleased internal decision still requires portal access.
        if (DB::table('application_decisions')->whereIn('application_id', $applicationIds)->whereNull('released_at')->exists()) {
            $reasons[] = 'result_not_released';
        }

        return $this->result(
            self::TYPE_REAL_APPLICANT,
            $reasons,
            $dueAt,
            $applications->count(),
            $periodIds,
            (bool) config('retention.applicants.real_applicant_enabled')
        );
    }

    /**
     * Retire accounts linked to ONE period (used right after the period is
     * confirmed COMPLETED, and nightly by the scheduler for stragglers).
     * Bounded + idempotent: already-processed accounts are safely skipped.
     *
     * @return array{checked:int,retired:int,blocked:int,failed:int}
     */
    public function cleanupPeriod(int $periodId, CarbonInterface $cutoff, int $batch = 100): array
    {
        $summary = ['checked' => 0, 'retired' => 0, 'blocked' => 0, 'failed' => 0];
        $userIds = PPDBRegistration::where('period_id', $periodId)
            ->whereNotNull('applicant_account_id')
            ->distinct()->pluck('applicant_account_id');

        foreach ($userIds->chunk(max(10, $batch)) as $chunk) {
            foreach (User::whereIn('id', $chunk->all())->orderBy('id')->get() as $user) {
                $summary['checked']++;
                try {
                    $result = $this->retire($user->id, $cutoff);
                    $summary[$result['status'] === 'retired' ? 'retired' : 'blocked']++;
                } catch (\Throwable $e) {
                    $summary['failed']++;
                    report($e);
                }
            }
        }

        return $summary;
    }

    /** @return array{status:string,type:string,reasons:list<string>,application_count:int} */
    public function retire(int $userId, CarbonInterface $cutoff): array
    {
        return DB::transaction(function () use ($userId, $cutoff): array {
            $user = User::whereKey($userId)->lockForUpdate()->first();
            if (! $user) {
                return ['status' => 'skipped', 'type' => 'missing', 'reasons' => ['account_missing'], 'application_count' => 0];
            }

            // Re-resolve under the lock so a new application or period state wins over a stale scan.
            $evaluation = $this->evaluate($user, $cutoff);
            if (! $evaluation['eligible']) {
                return [
                    'status' => 'skipped', 'type' => $evaluation['type'],
                    'reasons' => $evaluation['reasons'], 'application_count' => $evaluation['application_count'],
                ];
            }

            $applicationIds = $user->applicationsWithTrashed->pluck('id')->all();
            $email = $user->email;
            $accountId = $user->id;
            $user->forceFill([
                'is_active' => false,
                'retirement_started_at' => now(),
                'retirement_due_at' => $evaluation['due_at'],
                'remember_token' => null,
            ])->save();

            DB::table('sessions')->where('user_id', $accountId)->delete();
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            $user->notifications()->delete();

            if ($evaluation['type'] === self::TYPE_REAL_APPLICANT) {
                PPDBRegistration::withTrashed()->where('applicant_account_id', $accountId)->update(['applicant_account_id' => null]);
            }

            AuditService::system('applicant_account_retired', $user, [
                'account_id' => $accountId,
                'account_type' => $evaluation['type'],
                'application_ids' => $applicationIds,
                'period_ids' => $evaluation['period_ids'],
                'application_count' => count($applicationIds),
                'eligibility_reason' => $evaluation['type'],
                'retention_due_at' => $evaluation['due_at'],
                'action' => 'retire_login_account',
            ]);
            $user->delete();

            return [
                'status' => 'retired', 'type' => $evaluation['type'],
                'reasons' => [], 'application_count' => count($applicationIds),
            ];
        });
    }

    /** @param list<string> $reasons */
    private function evaluateUnverifiedOrphan(User $user, CarbonInterface $cutoff, array $reasons): array
    {
        $dueAt = $user->created_at?->copy()->addDays((int) config('retention.applicants.orphan_days', 3));
        if (! config('retention.applicants.orphan_enabled')) {
            $reasons[] = 'account_type_disabled';
        }
        if (! $dueAt || $dueAt->gt($cutoff)) {
            $reasons[] = 'orphan_retention_not_expired';
        }

        return $this->result(self::TYPE_UNVERIFIED_ORPHAN, $reasons, $dueAt, 0, [], true);
    }

    /** @param list<string> $reasons */
    private function evaluateVerifiedUnused(User $user, CarbonInterface $cutoff, array $reasons): array
    {
        $lastActivity = $user->last_activity_at ?? $user->updated_at ?? $user->created_at;
        $dueAt = $lastActivity?->copy()->addDays((int) config('retention.applicants.verified_unused_days', 180));
        if (! config('retention.applicants.verified_unused_enabled')) {
            $reasons[] = 'account_type_disabled';
        }
        if ($this->hasRelevantOpenOrUpcomingPeriod()) {
            $reasons[] = 'ppdb_access_window_relevant';
        }
        if (! $dueAt || $dueAt->gt($cutoff)) {
            $reasons[] = 'unused_retention_not_expired';
        }

        return $this->result(self::TYPE_VERIFIED_UNUSED, $reasons, $dueAt, 0, [], true);
    }

    private function hasRelevantOpenOrUpcomingPeriod(): bool
    {
        return PpdbPeriod::query()->get()->contains(
            fn (PpdbPeriod $period) => in_array($period->effectiveStatus(), [PpdbPeriod::STATUS_UPCOMING, PpdbPeriod::STATUS_OPEN], true)
        );
    }

    /** @param list<string> $reasons @param list<int> $periodIds */
    private function result(string $type, array $reasons, mixed $dueAt, int $applicationCount, array $periodIds, bool $typeEnabled): array
    {
        if (! $typeEnabled) {
            $reasons[] = 'account_type_disabled';
        }
        $reasons = array_values(array_unique($reasons));

        return [
            'eligible' => $reasons === [],
            'type' => $type,
            'reasons' => $reasons,
            'due_at' => $dueAt?->toIso8601String(),
            'application_count' => $applicationCount,
            'period_ids' => $periodIds,
        ];
    }
}
