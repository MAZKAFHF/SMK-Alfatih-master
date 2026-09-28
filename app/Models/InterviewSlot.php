<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InterviewSlot extends Model
{
    protected $fillable = [
        'period_id', 'date', 'start_time', 'end_time', 'location',
        'capacity', 'booked_count', 'status', 'notes', 'interviewer_id',
    ];

    protected $casts = [
        'date' => 'date',
        'capacity' => 'integer',
        'booked_count' => 'integer',
    ];

    public function period(): BelongsTo
    {
        return $this->belongsTo(PpdbPeriod::class, 'period_id');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(InterviewAppointment::class, 'slot_id');
    }

    public function remaining(): int
    {
        return max(0, $this->capacity - $this->booked_count);
    }

    public function isBookable(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }
        $slotStart = \Carbon\Carbon::parse($this->date->toDateString().' '.$this->start_time, 'Asia/Jakarta');

        return $slotStart->isFuture() && $this->remaining() > 0;
    }

    public function scopeAvailable($q)
    {
        return $q->where('status', 'active')->whereDate('date', '>=', now('Asia/Jakarta')->toDateString())->orderBy('date')->orderBy('start_time');
    }
}
