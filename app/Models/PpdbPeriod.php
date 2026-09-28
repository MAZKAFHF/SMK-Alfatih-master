<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class PpdbPeriod extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_UPCOMING = 'upcoming';
    public const STATUS_OPEN = 'open';
    public const STATUS_CLOSED = 'closed';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_ARCHIVED = 'archived';

    /** Status yang berarti "periode berjalan" (maksimal satu, lihat PpdbPeriodService). */
    public const CURRENT_STATUSES = [self::STATUS_UPCOMING, self::STATUS_OPEN];

    protected $fillable = [
        'academic_year', 'status', 'opens_at', 'closes_at', 'closed_at', 'results_released_at',
        'operational_completed_at', 'account_retention_until', 'is_open', 'status_override',
        'announcement', 'quota', 'contact_info', 'is_archived', 'is_active', 'created_by',
    ];

    protected $casts = [
        'opens_at' => 'datetime',
        'closes_at' => 'datetime',
        'closed_at' => 'datetime',
        'results_released_at' => 'datetime',
        'operational_completed_at' => 'datetime',
        'account_retention_until' => 'datetime',
        'is_open' => 'boolean',
        'is_archived' => 'boolean',
        'is_active' => 'boolean',
        'quota' => 'integer',
    ];

    public function applications(): HasMany
    {
        return $this->hasMany(PPDBRegistration::class, 'period_id');
    }

    public function slots(): HasMany
    {
        return $this->hasMany(InterviewSlot::class, 'period_id');
    }

    public static function active(): ?static
    {
        return \App\Services\PpdbContext::current();
    }

    public static function flushCache(): void
    {
        \App\Services\PpdbContext::flush();
    }

    /**
     * Status efektif: kolom `status` + guard tanggal + override.
     * - upcoming yang sudah lewat opens_at => open (untuk publik)
     * - open yang sudah lewat closes_at => closed (untuk publik)
     */
    public function effectiveStatus(): string
    {
        if ($this->status_override === 'open') {
            return self::STATUS_OPEN;
        }
        if ($this->status_override === 'closed') {
            return self::STATUS_CLOSED;
        }
        $now = now();
        if ($this->status === self::STATUS_UPCOMING && $this->opens_at && $now->gte($this->opens_at)) {
            return self::STATUS_OPEN;
        }
        if ($this->status === self::STATUS_OPEN && $this->closes_at && $now->gte($this->closes_at)) {
            return self::STATUS_CLOSED;
        }

        return $this->status ?? self::STATUS_DRAFT;
    }

    public function isCurrent(): bool
    {
        return in_array($this->effectiveStatus(), [self::STATUS_UPCOMING, self::STATUS_OPEN], true);
    }

    public function isHistory(): bool
    {
        return in_array($this->effectiveStatus(), [self::STATUS_CLOSED, self::STATUS_COMPLETED, self::STATUS_ARCHIVED], true);
    }

    /** Canonical signal that admissions work is finished; registration close alone is insufficient. */
    public function isAdmissionsCompleted(): bool
    {
        return in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_ARCHIVED], true)
            && $this->results_released_at !== null
            && $this->operational_completed_at !== null;
    }

    /**
     * True once the period is confirmed finished. All PPDB operational
     * mutations (verifikasi, booking, wawancara, keputusan) must refuse
     * work on such periods; completion is undone only via reopen.
     */
    public function isLockedForOperations(): bool
    {
        return $this->isAdmissionsCompleted();
    }

    public static function statusLabelFor(string $status): string
    {
        return match ($status) {
            self::STATUS_DRAFT => 'Draf',
            self::STATUS_UPCOMING => 'Akan Datang',
            self::STATUS_OPEN => 'Aktif',
            self::STATUS_CLOSED => 'Ditutup',
            self::STATUS_COMPLETED => 'Selesai',
            self::STATUS_ARCHIVED => 'Arsip',
            default => $status,
        };
    }

    public function badgeColor(): string
    {
        return match ($this->effectiveStatus()) {
            self::STATUS_OPEN => 'green',
            self::STATUS_UPCOMING => 'sky',
            self::STATUS_COMPLETED => 'green',
            self::STATUS_CLOSED => 'slate',
            self::STATUS_ARCHIVED => 'slate',
            default => 'slate',
        };
    }

    public function isOpen(): bool
    {
        return $this->effectiveStatus() === self::STATUS_OPEN;
    }

    public function statusLabel(): string
    {
        return match ($this->effectiveStatus()) {
            self::STATUS_DRAFT => 'Draf',
            self::STATUS_UPCOMING => 'Belum Dibuka',
            self::STATUS_OPEN => 'Sedang Dibuka',
            self::STATUS_CLOSED => 'Sudah Ditutup',
            self::STATUS_COMPLETED => 'Selesai',
            self::STATUS_ARCHIVED => 'Arsip',
            default => 'Draf',
        };
    }
}
