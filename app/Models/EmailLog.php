<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailLog extends Model
{
    protected $fillable = [
        'template', 'recipient', 'subject', 'payload', 'application_id', 'status', 'error', 'retries',
        'provider', 'provider_message_id', 'sent_at', 'delivered_at', 'bounced_at', 'complained_at',
        'last_event_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'bounced_at' => 'datetime',
        'complained_at' => 'datetime',
        'last_event_at' => 'datetime',
        'retries' => 'integer',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(PPDBRegistration::class, 'application_id');
    }
}
