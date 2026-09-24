<?php

namespace App\Models;

use App\Enums\ContentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Gallery extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'image',
        'category',
        'status',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'status' => ContentStatus::class,
        ];
    }

    public function scopePublished($query)
    {
        return $query->where('status', ContentStatus::Published);
    }

    public function getImageAttribute(?string $value): ?string
    {
        if (! $value) {
            return null;
        }
        if (! Storage::disk('public')->exists($value)) {
            return null;
        }
        return Storage::disk('public')->url($value);
    }
}
