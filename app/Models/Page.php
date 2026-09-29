<?php

namespace App\Models;

use App\Enums\ContentStatus;
use App\Services\HtmlSanitizer;
use App\Services\MediaService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Page extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'image',
        'meta_title',
        'meta_description',
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

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getImageAttribute(?string $value): ?string
    {
        if (! $value) {
            return null;
        }
        return MediaService::url($value);
    }

    public function getRawImage(): ?string
    {
        return $this->attributes['image'] ?? null;
    }

    protected static function booted(): void
    {
        static::saving(function (Page $page): void {
            if (! empty($page->content)) {
                $page->content = HtmlSanitizer::clean($page->content);
            }
        });
    }
}
