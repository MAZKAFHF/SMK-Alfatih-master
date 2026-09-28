<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicantNotification extends Model
{
    protected $fillable = ['account_id', 'application_id', 'title', 'message', 'action_url', 'read_at'];

    protected $casts = ['read_at' => 'datetime'];

    public function account(): BelongsTo
    {
        return $this->belongsTo(User::class, 'account_id');
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(PPDBRegistration::class, 'application_id');
    }

    public function scopeUnread($q)
    {
        return $q->whereNull('read_at');
    }
}
