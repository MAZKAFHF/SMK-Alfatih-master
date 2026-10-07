<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ExportPortableData extends Command
{
    protected $signature = 'app:data-export {path : Destination .json.gz path}';

    protected $description = 'Export persistent application data to a database-portable snapshot';

    /** Framework/runtime tables must never be copied between environments. */
    private const EXCLUDED_TABLES = [
        'cache', 'cache_locks', 'failed_jobs', 'job_batches', 'jobs',
        'migrations', 'password_reset_tokens', 'sessions',
    ];

    public function handle(): int
    {
        $path = $this->absolutePath((string) $this->argument('path'));
        if (! str_ends_with(strtolower($path), '.json.gz')) {
            $this->error('Path snapshot wajib berakhiran .json.gz.');

            return self::FAILURE;
        }

        $directory = dirname($path);
        if (! is_dir($directory) && ! mkdir($directory, 0700, true) && ! is_dir($directory)) {
            $this->error("Direktori snapshot tidak dapat dibuat: {$directory}");

            return self::FAILURE;
        }

        $tables = collect(Schema::getTableListing())
            ->map(fn (string $table) => $this->plainTableName($table))
            ->reject(fn (string $table) => in_array($table, self::EXCLUDED_TABLES, true))
            ->unique()
            ->sort()
            ->values();

        $payload = [
            'format' => 'smk-alfatih-portable-data',
            'version' => 1,
            'exported_at' => now()->toIso8601String(),
            'source_driver' => DB::connection()->getDriverName(),
            'tables' => [],
        ];

        foreach ($tables as $table) {
            $rows = DB::table($table)->orderBy($this->orderColumn($table))->get()
                ->map(fn (object $row) => (array) $row)
                ->all();
            $payload['tables'][$table] = $rows;
            $this->line("{$table}: ".count($rows));
        }

        $json = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        $compressed = gzencode($json, 9);
        if ($compressed === false || file_put_contents($path, $compressed, LOCK_EX) === false) {
            $this->error('Snapshot gagal ditulis.');

            return self::FAILURE;
        }

        @chmod($path, 0600);
        $this->info('Snapshot dibuat: '.$path);
        $this->info('SHA-256: '.hash_file('sha256', $path));

        return self::SUCCESS;
    }

    private function orderColumn(string $table): string
    {
        $columns = Schema::getColumnListing($table);

        return in_array('id', $columns, true) ? 'id' : $columns[0];
    }

    private function plainTableName(string $table): string
    {
        $parts = explode('.', $table);

        return end($parts);
    }

    private function absolutePath(string $path): string
    {
        if (preg_match('/^(?:[A-Za-z]:[\\\\\/]|\/)/', $path) === 1) {
            return $path;
        }

        return base_path($path);
    }
}
