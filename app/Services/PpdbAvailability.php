<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Models\PPDBRegistration;
use App\Models\PpdbPeriod;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

/**
 * SATU-SATUNYA otoritas status ketersediaan PPDB.
 *
 * Homepage, /ppdb, portal, dashboard admin, dan gerbang backend
 * WAJIB memakai service ini — dilarang menghitung status sendiri.
 *
 * Aturan batas waktu:
 * - opens_at tepat pada detiknya sudah OPEN.
 * - closes_at tepat pada detiknya sudah CLOSED.
 * Tidak ada cron untuk buka/tutup dasar.
 */
class PpdbAvailability
{
    public const NO_PERIOD = 'no_period';
    public const UPCOMING = 'upcoming';
    public const OPEN = 'open';
    public const FULL = 'full';
    public const CLOSED = 'closed';

    /** Status aplikasi yang mengonsumsi kuota (sudah submit final, belum batal). */
    public const COUNTED_STATUSES = [
        'submitted', 'needs_revision', 'resubmitted', 'verified',
        'waiting_slot', 'scheduled', 'interviewed', 'waiting_decision',
        'passed', 'not_passed',
    ];

    public function __construct(
        public readonly string $status,
        public readonly ?PpdbPeriod $period,
        public readonly ?CarbonImmutable $now,
        public readonly ?int $quota,
        public readonly int $usedQuota,
    ) {}

    public function remainingQuota(): ?int
    {
        return $this->quota === null ? null : max(0, $this->quota - $this->usedQuota);
    }

    public function canCreateApplication(): bool
    {
        return $this->status === self::OPEN;
    }

    /** Alias ringkas untuk pemanggil UI publik. */
    public function canRegister(): bool
    {
        return $this->canCreateApplication();
    }

    public function creationBlockedMessage(): string
    {
        return match ($this->status) {
            self::UPCOMING => $this->period?->opens_at
                ? 'Pendaftaran calon siswa belum dibuka. Silakan kembali pada '.
                    CarbonImmutable::parse($this->period->opens_at, 'Asia/Jakarta')
                        ->locale('id')->translatedFormat('j F Y, H.i').' WIB.'
                : 'Pendaftaran calon siswa belum dibuka. Silakan kembali pada jadwal pembukaan PPDB.',
            self::FULL => 'Kuota PPDB untuk periode ini telah terpenuhi.',
            self::CLOSED => 'Periode pendaftaran telah ditutup. Anda tetap dapat masuk ke Portal untuk melihat pendaftaran yang sudah ada.',
            default => 'Belum ada periode PPDB yang dibuka.',
        };
    }

    public function portalNotice(): string
    {
        return match ($this->status) {
            self::UPCOMING => 'Pendaftaran siswa baru belum dibuka.'.($this->period?->opens_at
                ? ' Pendaftaran dibuka pada '.CarbonImmutable::parse($this->period->opens_at, 'Asia/Jakarta')
                    ->locale('id')->translatedFormat('j F Y, H.i').' WIB.'
                : ''),
            self::FULL => 'Kuota PPDB telah terpenuhi.',
            self::CLOSED => 'Pendaftaran siswa baru untuk periode ini telah ditutup.',
            default => 'Belum ada periode PPDB yang dibuka.',
        };
    }

    public function publicLabel(): string
    {
        return match ($this->status) {
            self::UPCOMING => 'Belum Dibuka',
            self::OPEN => 'Sedang Dibuka',
            self::FULL => 'Kuota Telah Terpenuhi',
            self::CLOSED => 'Telah Ditutup',
            default => 'Belum Tersedia',
        };
    }

    public static function countedStatuses(): array
    {
        return self::COUNTED_STATUSES;
    }

    public static function usedQuota(int $periodId): int
    {
        return PPDBRegistration::where('period_id', $periodId)
            ->whereIn('application_status', self::COUNTED_STATUSES)
            ->count();
    }

    /**
     * Resolve untuk SATU periode (atau null => NO_PERIOD).
     * Wajib dipanggil per-request (jangan cache hasil resolved!).
     */
    public static function forPeriod(?PpdbPeriod $period, ?CarbonImmutable $now = null): static
    {
        $now ??= CarbonImmutable::now('Asia/Jakarta');
        if (! $period) {
            return new static(self::NO_PERIOD, null, $now, null, 0);
        }

        // Override darurat manual tetap dihormati.
        if ($period->status_override === 'closed') {
            return new static(self::CLOSED, $period, $now, $period->quota, static::usedQuota($period->id));
        }

        $forcedOpen = $period->status_override === 'open';

        $opensAt = $period->opens_at ? CarbonImmutable::parse($period->opens_at, 'Asia/Jakarta') : null;
        $closesAt = $period->closes_at ? CarbonImmutable::parse($period->closes_at, 'Asia/Jakarta') : null;

        if (! $forcedOpen && $period->status === PpdbPeriod::STATUS_DRAFT && ! $period->isOpen()) {
            // Draf yang tidak dibuka manual => perlakukan sebagai belum dibuka bila ada jadwal.
            if ($opensAt && $now->lt($opensAt)) {
                return new static(self::UPCOMING, $period, $now, $period->quota, static::usedQuota($period->id));
            }
            if (! $opensAt) {
                return new static(self::NO_PERIOD, $period, $now, $period->quota, static::usedQuota($period->id));
            }
        }

        if (! $forcedOpen && $opensAt && $now->lt($opensAt)) {
            return new static(self::UPCOMING, $period, $now, $period->quota, static::usedQuota($period->id));
        }
        if (! $forcedOpen && $closesAt && $now->gte($closesAt)) {
            return new static(self::CLOSED, $period, $now, $period->quota, static::usedQuota($period->id));
        }
        if (! $forcedOpen && ($period->status === PpdbPeriod::STATUS_CLOSED || $period->status === PpdbPeriod::STATUS_COMPLETED || $period->status === PpdbPeriod::STATUS_ARCHIVED)) {
            return new static(self::CLOSED, $period, $now, $period->quota, static::usedQuota($period->id));
        }

        $used = static::usedQuota($period->id);
        if ($period->quota !== null && $used >= $period->quota) {
            return new static(self::FULL, $period, $now, $period->quota, $used);
        }

        return new static(self::OPEN, $period, $now, $period->quota, $used);
    }

    /**
     * Periode yang ditampilkan ke publik: OPEN > UPCOMING terdekat > CLOSED terakhir.
     */
    public static function publicPeriod(): ?PpdbPeriod
    {
        // Sengaja tanpa cache. Endpoint pembuatan dan seluruh permukaan publik
        // harus melihat perubahan status/jadwal pada request yang sama.
        $current = PpdbPeriod::query()
            ->whereIn('status', PpdbPeriod::CURRENT_STATUSES)
            ->orderByDesc('id')
            ->first();
        if ($current) {
            return $current;
        }

        return PpdbPeriod::where('status', PpdbPeriod::STATUS_UPCOMING)
            ->whereNotNull('opens_at')
            ->orderBy('opens_at')
            ->first()
            ?? PpdbPeriod::whereIn('status', [PpdbPeriod::STATUS_CLOSED, PpdbPeriod::STATUS_COMPLETED, PpdbPeriod::STATUS_ARCHIVED])
                ->latest('id')
                ->first();
    }

    public static function resolvePublic(?CarbonImmutable $now = null): static
    {
        return static::forPeriod(static::publicPeriod(), $now);
    }

    /** Resolver otoritatif untuk GET/POST pembuatan aplikasi publik. */
    public static function resolveForApplicationCreation(?CarbonImmutable $now = null): static
    {
        return static::resolvePublic($now);
    }

    /**
     * Jalankan mutasi pembuatan aplikasi di bawah lock periode dan keputusan
     * availability terbaru. Callback hanya pernah dipanggil ketika OPEN.
     *
     * @template T
     * @param callable(PpdbPeriod, static): T $creator
     * @return T
     *
     * @throws ApplicationCreationUnavailableException
     */
    public static function createApplication(callable $creator, ?CarbonImmutable $now = null): mixed
    {
        return DB::transaction(function () use ($creator, $now) {
            $initial = static::resolveForApplicationCreation($now);
            if (! $initial->canCreateApplication() || ! $initial->period) {
                throw new ApplicationCreationUnavailableException($initial);
            }

            $period = PpdbPeriod::whereKey($initial->period->id)->lockForUpdate()->first();
            $live = static::forPeriod($period, $now);
            if (! $live->canCreateApplication() || ! $period) {
                throw new ApplicationCreationUnavailableException($live);
            }

            return $creator($period, $live);
        });
    }

    /**
     * Klaim satu slot kuota secara atomik di dalam transaksi submit.
     * HARUS dipanggil setelah lockForUpdate pada baris periode.
     *
     * @throws \App\Services\QuotaFullException
     */
    public static function claimOrFail(PpdbPeriod $period, ?CarbonImmutable $now = null): void
    {
        $state = static::forPeriod($period->fresh(), $now);
        if ($state->status !== self::OPEN) {
            throw new QuotaFullException(
                $state->status === self::FULL
                    ? 'Kuota PPDB baru saja terpenuhi. Pendaftaran Anda masih tersimpan sebagai draf, namun saat ini belum dapat dikirim.'
                    : 'Pendaftaran PPDB sedang tidak dibuka. Draf Anda tetap tersimpan.'
            );
        }
    }

    /**
     * Submit final dalam SATU transaksi: kunci periode -> cek kuota +
     * jendela waktu -> ubah status. Mencegah 101/100.
     *
     * @return array{to: ApplicationStatus, from: string}
     *
     * @throws \App\Services\QuotaFullException
     */
    public static function submitApplication(PPDBRegistration $app, int $actorId, ?CarbonImmutable $now = null): array
    {
        return DB::transaction(function () use ($app, $actorId, $now) {
            $period = PpdbPeriod::whereKey($app->period_id)->lockForUpdate()->firstOrFail();
            static::claimOrFail($period, $now);

            $from = $app->application_status->value;
            $to = $from === 'needs_revision' ? ApplicationStatus::Resubmitted : ApplicationStatus::Submitted;
            $app->update([
                'application_status' => $to, 'status' => 'pending',
                'submitted_at' => now(),
                'sequence' => $app->sequence ?? (PPDBRegistration::where('period_id', $app->period_id)->max('sequence') ?? 0) + 1,
            ]);
            \App\Models\StatusHistory::create([
                'application_id' => $app->id, 'from_status' => $from,
                'to_status' => $to->value, 'actor_id' => $actorId,
                'note' => 'Submit final oleh pemohon',
            ]);

            return ['to' => $to, 'from' => $from];
        });
    }
}
