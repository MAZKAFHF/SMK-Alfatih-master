<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RescheduleRequest extends Model
{
    protected $fillable = [
        'appointment_id', 'old_slot_id', 'new_slot_id', 'reason',
        'status', 'admin_note', 'decided_by', 'decided_at',
    ];

    protected $casts = ['decided_at' => 'datetime'];

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(InterviewAppointment::class, 'appointment_id');
    }

    public function oldSlot(): BelongsTo
    {
        return $this->belongsTo(InterviewSlot::class, 'old_slot_id');
    }

    public function newSlot(): BelongsTo
    {
        return $this->belongsTo(InterviewSlot::class, 'new_slot_id');
    }
}
