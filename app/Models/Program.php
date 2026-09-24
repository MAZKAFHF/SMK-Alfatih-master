<?php

namespace App\Models;

use App\Enums\ProgramStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Program extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'short_description',
        'description',
        'image',
        'status',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'status' => ProgramStatus::class,
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('status', ProgramStatus::Active);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
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
