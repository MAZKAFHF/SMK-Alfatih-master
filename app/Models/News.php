<?php

namespace App\Models;

use App\Enums\ContentStatus;
use App\Services\HtmlSanitizer;
use App\Services\MediaService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class News extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'thumbnail',
        'content',
        'status',
        'author_id',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ContentStatus::class,
            'published_at' => 'datetime',
        ];
    }

    public function scopePublished($query)
    {
        return $query->where('status', ContentStatus::Published)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getThumbnailAttribute(?string $value): ?string
    {
        if (! $value) {
            return null;
        }
        return MediaService::url($value);
    }

    protected static function booted(): void
    {
        static::saving(function (News $news): void {
            if (! empty($news->content)) {
                $news->content = HtmlSanitizer::clean($news->content);
            }
        });
    }

    public function getExcerptAttribute(?string $value): string
    {
        return Str::limit(strip_tags((string) $this->content), 160);
    }
}
