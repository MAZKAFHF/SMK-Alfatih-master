<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChangeRequest extends Model
{
    protected $fillable = [
        'application_id', 'section', 'field', 'old_value', 'new_value',
        'reason', 'status', 'admin_note', 'decided_by', 'decided_at',
    ];

    protected $casts = ['decided_at' => 'datetime'];

    public function application(): BelongsTo
    {
        return $this->belongsTo(PPDBRegistration::class, 'application_id');
    }
}
