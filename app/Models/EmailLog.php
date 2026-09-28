<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailLog extends Model
{
    protected $fillable = [
        'template', 'recipient', 'subject', 'payload', 'application_id', 'status', 'error', 'retries', 'sent_at',
    ];

    protected $casts = ['payload' => 'array', 'sent_at' => 'datetime', 'retries' => 'integer'];

    public function application(): BelongsTo
    {
        return $this->belongsTo(PPDBRegistration::class, 'application_id');
    }
}
