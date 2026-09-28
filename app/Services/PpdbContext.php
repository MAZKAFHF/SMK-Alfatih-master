<?php

namespace App\Services;

use App\Models\PpdbPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * SATU-SATUNYA resolver konteks periode PPDB.
 * DILARANG menduplikasi logika pemilihan periode di controller lain.
 *
 * Mode dashboard: active | upcoming | history | empty
 */
class PpdbContext
{
    public const MODE_ACTIVE = 'active';
    public const MODE_UPCOMING = 'upcoming';
    public const MODE_HISTORY = 'history';
    public const MODE_EMPTY = 'empty';

    public static function current(): ?PpdbPeriod
    {
        return Cache::remember('ppdb:active:id', 300, function () {
            return PpdbPeriod::whereIn('status', PpdbPeriod::CURRENT_STATUSES)
                ->latest('id')
                ->first();
        });
    }

    public static function latestClosed(): ?PpdbPeriod
    {
        return Cache::remember('ppdb:latest-closed:id', 300, function () {
            return PpdbPeriod::whereIn('status', [PpdbPeriod::STATUS_CLOSED, PpdbPeriod::STATUS_COMPLETED, PpdbPeriod::STATUS_ARCHIVED])
                ->latest('id')
                ->first();
        });
    }

    /** @return array<int, array{id: int, academic_year: string, status: string}> */
    public static function options(): array
    {
        return Cache::remember('ppdb:periods:all', 300, function () {
            return PpdbPeriod::orderByDesc('id')->get(['id', 'academic_year', 'status'])
                ->map(fn ($p) => ['id' => $p->id, 'academic_year' => $p->academic_year, 'status' => $p->effectiveStatus()])
                ->all();
        });
    }

    /**
     * @return array{period: ?PpdbPeriod, mode: string}
     */
    public static function resolve(?int $requestedId = null): array
    {
        if ($requestedId) {
            $period = PpdbPeriod::find($requestedId);
            abort_unless($period, 404, 'Periode PPDB tidak ditemukan.');

            return ['period' => $period, 'mode' => static::modeFor($period)];
        }

        if ($current = static::current()) {
            return ['period' => $current, 'mode' => static::modeFor($current)];
        }

        if ($closed = static::latestClosed()) {
            return ['period' => $closed, 'mode' => static::MODE_HISTORY];
        }

        return ['period' => null, 'mode' => static::MODE_EMPTY];
    }

    public static function resolveFromRequest(Request $request): array
    {
        $id = $request->filled('period') ? (int) $request->input('period') : ($request->filled('period_id') ? (int) $request->input('period_id') : null);

        return static::resolve($id);
    }

    public static function modeFor(PpdbPeriod $period): string
    {
        return match ($period->effectiveStatus()) {
            PpdbPeriod::STATUS_OPEN => static::MODE_ACTIVE,
            PpdbPeriod::STATUS_UPCOMING => static::MODE_UPCOMING,
            PpdbPeriod::STATUS_CLOSED, PpdbPeriod::STATUS_COMPLETED, PpdbPeriod::STATUS_ARCHIVED => static::MODE_HISTORY,
            default => static::MODE_HISTORY,
        };
    }

    public static function modeLabel(string $mode): string
    {
        return match ($mode) {
            static::MODE_ACTIVE => 'Aktif',
            static::MODE_UPCOMING => 'Akan Datang',
            static::MODE_HISTORY => 'Riwayat',
            default => 'Belum Ada',
        };
    }

    public static function flush(): void
    {
        Cache::forget('ppdb:active:id');
        Cache::forget('ppdb:latest-closed:id');
        Cache::forget('ppdb:periods:all');
        Cache::forget('ppdb_period:active');
    }
}
