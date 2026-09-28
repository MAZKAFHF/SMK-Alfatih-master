<?php

namespace App\Console\Commands;

use App\Enums\ApplicationStatus;
use App\Models\PPDBRegistration;
use App\Models\StatusHistory;
use App\Services\InterviewCompletion;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Backfill aman untuk status penyelesaian wawancara.
 *
 * HANYA menandai completion bila bukti yang ada membuktikan selesai:
 *  - appointment.status=attended + assessment.attendance=attended
 *    tetapi application_status masih tertinggal di scheduled
 *    => naikkan ke waiting_decision (deterministik).
 *
 * TIDAK PERNAH:
 *  - migrate:fresh, hapus data, timpa asesmen, mengarang nilai.
 *  - menandai ambiguous records sebagai selesai.
 */
class BackfillInterviewCompletion extends Command
{
    protected $signature = 'ppdb:backfill-interview-completion {--dry-run : Tampilkan tanpa mengubah}';
    protected $description = 'Perbaiki status wawancara yang tertinggal tanpa menimpa asesmen';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $fixed = 0;
        $ambiguous = 0;
        $contradictions = 0;

        $apps = PPDBRegistration::with(['appointment.assessment', 'decision'])->get();

        foreach ($apps as $app) {
            $completed = InterviewCompletion::isCompleted($app);
            $hasDecision = (bool) $app->decision;

            // Kontradiksi: decision ada tapi kanonis bilang belum selesai.
            if ($hasDecision && ! $completed) {
                $contradictions++;
                $this->warn("Kontradiksi #{$app->id} {$app->registration_number}: decision={$app->decision->result->value} tapi wawancara dianggap belum selesai (".InterviewCompletion::reason($app).'). Dilaporkan, TIDAK diubah.');
                continue;
            }

            // Deterministik: bukti appointment+assessment lengkap tapi status tertinggal.
            $appt = $app->appointment;
            $proof = $appt && $appt->attended_at && ($appt->assessment?->attendance === 'attended');
            $stuck = in_array($app->application_status, [ApplicationStatus::Scheduled, ApplicationStatus::Verified, ApplicationStatus::WaitingSlot], true);

            if ($proof && $stuck) {
                // Hanya aman bila belum ada keputusan (jangan geser passed/not_passed).
                if (in_array($app->application_status, [ApplicationStatus::Passed, ApplicationStatus::NotPassed], true)) {
                    continue;
                }
                if ($dry) {
                    $this->info("[dry-run] #{$app->id} {$app->registration_number}: {$app->application_status->value} -> waiting_decision");
                } else {
                    DB::transaction(function () use ($app) {
                        $from = $app->application_status->value;
                        $app->update(['application_status' => ApplicationStatus::WaitingDecision, 'status' => 'pending']);
                        StatusHistory::create([
                            'application_id' => $app->id,
                            'from_status' => $from,
                            'to_status' => 'waiting_decision',
                            'actor_id' => null,
                            'note' => 'Backfill: wawancara terbukti selesai (appointment+assessment), status disinkronkan',
                        ]);
                    });
                    $this->info("Fixed #{$app->id} {$app->registration_number}");
                }
                $fixed++;
                continue;
            }

            // Ambiguous: status passed tanpa appointment/decision yang jelas, dsb.
            if (! $completed && ! $proof) {
                // Hanya hitung yang berpotensi legacy (passed tanpa bukti) sebagai ambiguous.
                if ($app->application_status === ApplicationStatus::Passed || $app->application_status === ApplicationStatus::NotPassed) {
                    if (! $hasDecision) {
                        $ambiguous++;
                    }
                }
            }
        }

        $this->info("Selesai. Diperbaiki: {$fixed}. Kontradiksi decision-vs-wawancara: {$contradictions}. Ambiguous: {$ambiguous}.");

        return self::SUCCESS;
    }
}
