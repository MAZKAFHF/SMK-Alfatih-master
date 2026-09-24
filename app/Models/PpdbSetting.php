<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PpdbSetting extends Model
{
    protected $fillable = [
        'academic_year',
        'opens_at',
        'closes_at',
        'is_open',
        'status_override',
        'announcement',
        'quota',
        'contact_info',
    ];

    protected $casts = [
        'opens_at' => 'datetime',
        'closes_at' => 'datetime',
        'is_open' => 'boolean',
        'quota' => 'integer',
    ];

    public static function current(): static
    {
        return Cache::remember('ppdb_setting:current', 3600, function () {
            return static::first() ?? static::create(['academic_year' => now()->year.'/'.(now()->year + 1)]);
        });
    }

    public function isOpen(): bool
    {
        if ($this->status_override === 'open') {
            return true;
        }
        if ($this->status_override === 'closed') {
            return false;
        }
        if (! $this->is_open) {
            return false;
        }
        if ($this->opens_at && now()->lt($this->opens_at)) {
            return false;
        }
        if ($this->closes_at && now()->gt($this->closes_at)) {
            return false;
        }

        return true;
    }

    public function statusLabel(): string
    {
        if (! $this->isOpen()) {
            if ($this->opens_at && now()->lt($this->opens_at)) {
                return 'Belum Dibuka';
            }

            return 'Sudah Ditutup';
        }

        return 'Sedang Dibuka';
    }

    public static function flushCache(): void
    {
        Cache::forget('ppdb_setting:current');
    }
}
