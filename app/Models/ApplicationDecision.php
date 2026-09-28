<?php

namespace App\Models;

use App\Enums\DecisionResult;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationDecision extends Model
{
    protected $fillable = [
        'application_id', 'result', 'decided_by', 'decided_at',
        'internal_note', 'applicant_message', 'released_at',
    ];

    protected $casts = [
        'result' => DecisionResult::class,
        'decided_at' => 'datetime',
        'released_at' => 'datetime',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(PPDBRegistration::class, 'application_id');
    }

    public function isReleased(): bool
    {
        return $this->released_at !== null;
    }
}
