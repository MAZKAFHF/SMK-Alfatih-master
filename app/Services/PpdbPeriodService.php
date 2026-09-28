<?php

namespace App\Services;

use App\Models\PpdbPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PpdbPeriodService
{
    /**
     * Buat periode baru. Menolak bila ada periode berjalan lain
     * (upcoming/open) atau window tanggal tumpang-tindih dengannya.
     */
    public static function create(array $data, ?int $actorId = null): PpdbPeriod
    {
        return DB::transaction(function () use ($data, $actorId) {
            $status = $data['status'] ?? PpdbPeriod::STATUS_DRAFT;
            // Draf dateless boleh disiapkan kapan pun. Status berjalan /
            // window bertanggal dicek terhadap periode berjalan lain.
            if (in_array($status, PpdbPeriod::CURRENT_STATUSES, true)) {
                static::assertNoOtherCurrent($data['opens_at'] ?? null, $data['closes_at'] ?? null);
            } elseif (! empty($data['opens_at']) && ! empty($data['closes_at'])) {
                static::assertWindowFree($data['opens_at'], $data['closes_at']);
            }

            $period = PpdbPeriod::create(array_merge($data, [
                'status' => $data['status'] ?? PpdbPeriod::STATUS_DRAFT,
                'created_by' => $actorId,
            ]));
            PpdbContext::flush();
            AuditService::log('period_created', $period);

            return $period;
        });
    }

    /**
     * Buka periode (draft/upcoming -> open). Transaksi + kunci:
     * maksimal satu periode berjalan dalam satu waktu.
     */
    public static function open(PpdbPeriod $period, ?int $actorId = null): PpdbPeriod
    {
        return DB::transaction(function () use ($period, $actorId) {
            $conflict = PpdbPeriod::whereIn('status', PpdbPeriod::CURRENT_STATUSES)
                ->where('id', '!=', $period->id)
                ->lockForUpdate()
                ->first();
            if ($conflict) {
                throw ValidationException::withMessages([
                    'status' => 'Tidak dapat dibuka: periode '.$conflict->academic_year.' masih berjalan. Tutup/arsipkan dulu.',
                ]);
            }
            $period->update(['status' => PpdbPeriod::STATUS_OPEN, 'is_active' => true, 'is_archived' => false]);
            PpdbContext::flush();
            AuditService::log('period_opened', $period);

            return $period->fresh();
        });
    }

    public static function close(PpdbPeriod $period, ?int $actorId = null, ?string $note = null): PpdbPeriod
    {
        $period->update([
            'status' => PpdbPeriod::STATUS_CLOSED,
            'closed_at' => now(),
            'is_active' => false,
        ]);
        PpdbContext::flush();
        AuditService::log('period_closed', $period, null, ['note' => $note]);

        return $period->fresh();
    }

    public static function reopen(PpdbPeriod $period, ?int $actorId = null): PpdbPeriod
    {
        return DB::transaction(function () use ($period, $actorId) {
            $conflict = PpdbPeriod::whereIn('status', PpdbPeriod::CURRENT_STATUSES)
                ->where('id', '!=', $period->id)
                ->lockForUpdate()
                ->first();
            if ($conflict) {
                throw ValidationException::withMessages([
                    'status' => 'Tidak dapat dibuka kembali: periode '.$conflict->academic_year.' sedang berjalan.',
                ]);
            }
            $period->update([
                'status' => PpdbPeriod::STATUS_OPEN, 'is_active' => true, 'closed_at' => null,
                // Reset baseline pensiun agar tanggal basi tidak menyebabkan
                // cleanup prematur; dihitung ulang saat ditandai selesai lagi.
                // results_released_at dipertahankan sebagai fakta historis.
                'operational_completed_at' => null,
                'account_retention_until' => null,
            ]);
            PpdbContext::flush();
            AuditService::log('period_reopened', $period);

            return $period->fresh();
        });
    }

    public static function archive(PpdbPeriod $period, ?int $actorId = null): PpdbPeriod
    {
        if (in_array($period->status, PpdbPeriod::CURRENT_STATUSES, true)) {
            throw ValidationException::withMessages([
                'status' => 'Periode berjalan tidak dapat diarsipkan. Tutup dulu.',
            ]);
        }
        if ($period->status === PpdbPeriod::STATUS_COMPLETED) {
            throw ValidationException::withMessages([
                'status' => 'Periode yang sudah ditandai selesai tidak perlu diarsipkan lagi.',
            ]);
        }
        // Archive marks operational work finished. Stamp completion so the
        // canonical isAdmissionsCompleted() can become true once results are
        // released; account retention still requires its own deadline.
        // close() alone never triggers account cleanup (closed != completed).
        $period->update([
            'status' => PpdbPeriod::STATUS_ARCHIVED, 'is_archived' => true, 'is_active' => false,
            'operational_completed_at' => $period->operational_completed_at ?? now(),
        ]);
        PpdbContext::flush();
        AuditService::log('period_archived', $period);

        return $period->fresh();
    }

    /**
     * Tandai periode sebagai SELESAI: admin memastikan seluruh proses PPDB
     * berakhir. Berbeda dari TUTUP (yang hanya menghentikan pendaftaran):
     * akun pemohon tetap aktif saat ditutup, dan workflow masih berjalan.
     *
     * Mengunci mutasi operasional periode (lihat isLockedForOperations) dan
     * menjadi pemicu eligible pembersihan akun pemohon.
     *
     * @throws ValidationException bila masih ada pekerjaan tertunda.
     */
    public static function complete(PpdbPeriod $period, ?int $actorId = null): PpdbPeriod
    {
        return DB::transaction(function () use ($period, $actorId) {
            $period = PpdbPeriod::whereKey($period->id)->lockForUpdate()->firstOrFail();

            if ($period->status === PpdbPeriod::STATUS_COMPLETED) {
                return $period;
            }
            if ($period->status !== PpdbPeriod::STATUS_CLOSED) {
                throw ValidationException::withMessages([
                    'status' => 'Hanya periode yang sudah ditutup yang dapat ditandai selesai. Tutup periode dulu.',
                ]);
            }

            $appIds = \App\Models\PPDBRegistration::where('period_id', $period->id)->pluck('id');

            $pendingVerification = \App\Models\PPDBRegistration::where('period_id', $period->id)
                ->whereIn('application_status', ['submitted', 'resubmitted', 'needs_revision'])->count();
            if ($pendingVerification > 0) {
                throw ValidationException::withMessages([
                    'status' => "Masih ada {$pendingVerification} pendaftaran menunggu verifikasi/perbaikan. Selesaikan dulu sebelum menandai selesai.",
                ]);
            }

            $pendingWorkflow = \App\Models\PPDBRegistration::where('period_id', $period->id)
                ->whereIn('application_status', ['verified', 'waiting_slot', 'scheduled', 'interviewed', 'waiting_decision'])->count();
            if ($pendingWorkflow > 0) {
                throw ValidationException::withMessages([
                    'status' => "Masih ada {$pendingWorkflow} pendaftaran dalam proses wawancara/keputusan. Selesaikan dulu sebelum menandai selesai.",
                ]);
            }

            if ($appIds->isNotEmpty()) {
                $pendingCorrection = \Illuminate\Support\Facades\DB::table('change_requests')
                    ->whereIn('application_id', $appIds)->where('status', 'pending')->count();
                if ($pendingCorrection > 0) {
                    throw ValidationException::withMessages([
                        'status' => "Masih ada {$pendingCorrection} permohonan koreksi menunggu keputusan.",
                    ]);
                }
                $pendingReschedule = \Illuminate\Support\Facades\DB::table('reschedule_requests')
                    ->join('interview_appointments', 'interview_appointments.id', '=', 'reschedule_requests.appointment_id')
                    ->whereIn('interview_appointments.application_id', $appIds)
                    ->where('reschedule_requests.status', 'pending')->count();
                if ($pendingReschedule > 0) {
                    throw ValidationException::withMessages([
                        'status' => "Masih ada {$pendingReschedule} permohonan ubah jadwal menunggu keputusan.",
                    ]);
                }
                $unreleased = \Illuminate\Support\Facades\DB::table('application_decisions')
                    ->join('ppdb_registrations', 'ppdb_registrations.id', '=', 'application_decisions.application_id')
                    ->where('ppdb_registrations.period_id', $period->id)
                    ->whereIn('ppdb_registrations.application_status', ['passed', 'not_passed'])
                    ->whereNull('application_decisions.released_at')->count();
                if ($unreleased > 0) {
                    throw ValidationException::withMessages([
                        'status' => "Masih ada {$unreleased} keputusan belum dirilis ke pendaftar. Rilis dulu sebelum menandai selesai.",
                    ]);
                }
            }

            $operationalAt = $period->operational_completed_at ?? now();
            // SYSTEM-DRIVEN: batas retensi dihitung sistem (bukan diketik admin).
            // Nilai eksplisit lama dipertahankan; jika kosong, turunkan dari
            // konfigurasi retensi terpusat agar panel read-only selalu tepat.
            $retentionUntil = $period->account_retention_until
                ?? $operationalAt->copy()->addDays((int) config('retention.applicants.real_retention_days', 90));
            $period->update([
                'status' => PpdbPeriod::STATUS_COMPLETED,
                'is_active' => false,
                'is_archived' => false,
                'results_released_at' => $period->results_released_at ?? now(),
                'operational_completed_at' => $operationalAt,
                'account_retention_until' => $retentionUntil,
            ]);
            PpdbContext::flush();
            AuditService::log('period_completed', $period);

            return $period->fresh();
        });
    }

    /** @throws ValidationException */
    private static function assertNoOtherCurrent(mixed $opensAt, mixed $closesAt): void
    {
        $other = PpdbPeriod::whereIn('status', PpdbPeriod::CURRENT_STATUSES)->first();
        if ($other) {
            throw ValidationException::withMessages([
                'academic_year' => 'Periode '.$other->academic_year.' masih berjalan. Tutup/arsipkan dulu sebelum membuat periode baru.',
            ]);
        }
        if ($opensAt && $closesAt) {
            static::assertWindowFree($opensAt, $closesAt);
        }
    }

    /** @throws ValidationException */
    private static function assertWindowFree(mixed $opensAt, mixed $closesAt): void
    {
        $overlap = PpdbPeriod::whereIn('status', PpdbPeriod::CURRENT_STATUSES)
            ->where(fn ($q) => $q->whereNull('opens_at')->orWhere('opens_at', '<=', $closesAt))
            ->where(fn ($q) => $q->whereNull('closes_at')->orWhere('closes_at', '>=', $opensAt))
            ->exists();
        if ($overlap) {
            throw ValidationException::withMessages([
                'opens_at' => 'Rentang tanggal tumpang-tindih dengan periode berjalan lain.',
            ]);
        }
    }
}
