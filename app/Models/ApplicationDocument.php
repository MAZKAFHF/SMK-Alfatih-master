<?php

namespace App\Models;

use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApplicationDocument extends Model
{
    protected $fillable = [
        'application_id', 'type', 'status', 'disk', 'path', 'original_name',
        'mime', 'size', 'version', 'admin_note', 'reviewed_by', 'reviewed_at',
    ];

    protected $casts = [
        'type' => DocumentType::class,
        'status' => DocumentStatus::class,
        'reviewed_at' => 'datetime',
        'size' => 'integer',
        'version' => 'integer',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(PPDBRegistration::class, 'application_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(DocumentRevision::class, 'document_id')->orderByDesc('version');
    }
}
