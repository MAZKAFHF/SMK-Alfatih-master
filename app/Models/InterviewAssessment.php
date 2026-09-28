<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InterviewAssessment extends Model
{
    protected $fillable = [
        'appointment_id', 'attendance', 'interview_notes',
        'tahfizh_notes', 'tahsin_notes', 'recommendation', 'assessor_id', 'assessed_at',
    ];

    protected $casts = ['assessed_at' => 'datetime'];

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(InterviewAppointment::class, 'appointment_id');
    }
}
