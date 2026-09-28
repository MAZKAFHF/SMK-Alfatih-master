<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'actor_type',
        'action',
        'auditable_type',
        'auditable_id',
        'auditable_label',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function auditable()
    {
        return $this->morphTo();
    }

    public static function record(?User $actor, string $action, ?Model $auditable = null, ?array $old = null, ?array $new = null): self
    {
        $request = request();

        return static::create([
            'user_id' => $actor?->id,
            'actor_type' => $actor ? 'user' : 'system',
            'action' => $action,
            'auditable_type' => $auditable ? get_class($auditable) : null,
            'auditable_id' => $auditable?->getKey(),
            'auditable_label' => $auditable ? (string) ($auditable->registration_number ?? $auditable->title ?? $auditable->name ?? $auditable->email ?? $auditable->id) : null,
            'old_values' => $old,
            'new_values' => $new,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'created_at' => now(),
        ]);
    }

    public static function recordSystem(string $action, ?Model $auditable = null, ?array $metadata = null): self
    {
        return static::record(null, $action, $auditable, null, $metadata);
    }
}
