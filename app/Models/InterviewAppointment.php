<?php

namespace App\Models;

use App\Enums\InterviewAppointmentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class InterviewAppointment extends Model
{
    protected $fillable = ['application_id', 'slot_id', 'status', 'booked_at', 'attended_at'];

    protected $casts = [
        'status' => InterviewAppointmentStatus::class,
        'booked_at' => 'datetime',
        'attended_at' => 'datetime',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(PPDBRegistration::class, 'application_id');
    }

    public function slot(): BelongsTo
    {
        return $this->belongsTo(InterviewSlot::class, 'slot_id');
    }

    public function reschedules(): HasMany
    {
        return $this->hasMany(RescheduleRequest::class, 'appointment_id');
    }

    public function assessment(): HasOne
    {
        return $this->hasOne(InterviewAssessment::class, 'appointment_id');
    }
}
