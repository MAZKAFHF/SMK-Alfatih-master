<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MediaAudit extends Command
{
    protected $signature = 'app:media-audit {--fix : Delete DB references to missing files? No, only report}';
    protected $description = 'Audit DB media references vs storage existence (no auto-delete)';

    public function handle(): int
    {
        $checks = [
            'programs' => ['model' => \App\Models\Program::class, 'field' => 'image'],
            'news' => ['model' => \App\Models\News::class, 'field' => 'thumbnail'],
            'galleries' => ['model' => \App\Models\Gallery::class, 'field' => 'image'],
            'pages' => ['model' => \App\Models\Page::class, 'field' => 'image'],
            'site_settings logo' => ['model' => \App\Models\SiteSetting::class, 'field' => 'value', 'filter' => ['key' => 'logo']],
            'site_settings favicon' => ['model' => \App\Models\SiteSetting::class, 'field' => 'value', 'filter' => ['key' => 'favicon']],
        ];

        $missing = 0;
        $total = 0;

        foreach ($checks as $label => $cfg) {
            $model = $cfg['model'];
            $field = $cfg['field'];
            $query = $model::query();
            if (isset($cfg['filter'])) {
                foreach ($cfg['filter'] as $k => $v) {
                    $query->where($k, $v);
                }
            }
            // Only where field not null
            $query->whereNotNull($field)->where($field, '!=', '');

            $rows = $query->get();
            foreach ($rows as $row) {
                $total++;
                // Use raw DB value, not accessor URL
                $path = $row->getRawOriginal($field) ?? $row->{$field};
                if (str_starts_with((string) $path, 'http') || str_starts_with((string) $path, '/storage')) {
                    $this->warn("[$label] ID {$row->id}: stores URL not relative: $path");
                    $missing++;
                    continue;
                }
                if (! \Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
                    $this->warn("[$label] ID {$row->id}: missing file: $path");
                    $missing++;
                }
            }
        }

        // Check orphan files (in storage but not in DB)
        $allPaths = collect($checks)->flatMap(function ($cfg) {
            $model = $cfg['model'];
            $field = $cfg['field'];
            $q = $model::query();
            if (isset($cfg['filter'])) {
                foreach ($cfg['filter'] as $k => $v) {
                    $q->where($k, $v);
                }
            }
            // Use raw values to avoid accessor URLs
            $rows = $q->whereNotNull($field)->get();
            return $rows->map(fn ($r) => $r->getRawOriginal($field) ?? $r->{$field});
        })->filter()->unique()->values()->all();

        $files = \Illuminate\Support\Facades\Storage::disk('public')->allFiles();
        $orphans = array_diff($files, $allPaths);
        // Filter out .gitignore and backups
        $orphans = array_filter($orphans, fn ($f) => ! str_contains($f, '.gitignore') && ! str_starts_with($f, 'backups/'));

        $this->info("Checked $total DB references, $missing missing, ".count($orphans)." orphan files");
        if ($missing > 0) {
            $this->warn("Missing files indicate DB points to non-existent storage. Fix by re-uploading or clearing DB field.");
        }
        if (count($orphans) > 0) {
            $this->info("Orphan files (not in DB): ".implode(', ', array_slice($orphans, 0, 10)));
            if (count($orphans) > 10) {
                $this->info("... and ".(count($orphans) - 10)." more");
            }
        }

        return $missing > 0 ? self::FAILURE : self::SUCCESS;
    }
}
