<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContactMessage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'subject',
        'message',
        'status',
        'is_read',
        'read_at',
        'is_archived',
        'handling_status',
        'response_channel',
        'admin_note',
        'resolved_by',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'string',
            'is_read' => 'boolean',
            'is_archived' => 'boolean',
            'read_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public function scopeUnread($q)
    {
        return $q->where('is_read', false);
    }

    public function scopeRead($q)
    {
        return $q->where('is_read', true);
    }
}
