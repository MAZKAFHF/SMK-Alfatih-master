<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\ContactMessage;
use App\Models\Gallery;
use App\Models\News;
use App\Models\Page;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TrashPurgeService
{
    /** @return array<string, class-string<Model>> */
    public function models(): array
    {
        $models = [
            'news' => News::class,
            'galleries' => Gallery::class,
            'announcements' => Announcement::class,
            'pages' => Page::class,
        ];

        if (config('retention.trash.include_contact_messages')) {
            $models['contact_messages'] = ContactMessage::class;
        }

        return $models;
    }

    public function eligibleCount(string $model, CarbonInterface $cutoff): int
    {
        return $model::onlyTrashed()->where('deleted_at', '<=', $cutoff)->count();
    }

    /**
     * @return array{status:string,label:?string}
     */
    public function purgeOne(string $model, int $id, CarbonInterface $cutoff): array
    {
        return DB::transaction(function () use ($model, $id, $cutoff): array {
            /** @var Model|null $item */
            $item = $model::onlyTrashed()->whereKey($id)->lockForUpdate()->first();
            if (! $item || ! $item->deleted_at || $item->deleted_at->gt($cutoff)) {
                return ['status' => 'skipped', 'label' => null];
            }

            $label = (string) ($item->title ?? $item->subject ?? $item->getKey());
            $this->deleteOwnedFile($item);
            $item->forceDelete();

            AuditService::system('trash_auto_purged', null, [
                'model' => $model,
                'record_id' => $id,
                'deleted_at' => $item->deleted_at?->toIso8601String(),
                'cutoff' => $cutoff->toIso8601String(),
            ]);

            $this->clearCache($model);

            return ['status' => 'purged', 'label' => $label];
        });
    }

    private function deleteOwnedFile(Model $item): void
    {
        $path = match (true) {
            $item instanceof News => $item->getRawOriginal('thumbnail'),
            $item instanceof Gallery, $item instanceof Page => $item->getRawOriginal('image'),
            default => null,
        };

        MediaService::delete($path);
    }

    private function clearCache(string $model): void
    {
        match ($model) {
            News::class => PublicCacheService::forgetNews(),
            Gallery::class => PublicCacheService::forgetGalleries(),
            Announcement::class => PublicCacheService::forgetAnnouncements(),
            Page::class => PublicCacheService::forgetPages(),
            default => null,
        };
    }
}
