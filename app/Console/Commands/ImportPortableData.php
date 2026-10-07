<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class ImportPortableData extends Command
{
    protected $signature = 'app:data-import {path : Source .json.gz path} {--force : Confirm destructive replacement}';

    protected $description = 'Replace local persistent application data from a portable snapshot';

    public function handle(): int
    {
        if (app()->environment('production')) {
            $this->error('Import snapshot dilarang di production.');

            return self::FAILURE;
        }
        if (! $this->option('force')) {
            $this->error('Import mengganti data lokal. Jalankan kembali dengan --force.');

            return self::FAILURE;
        }

        $path = $this->absolutePath((string) $this->argument('path'));
        if (! is_file($path)) {
            $this->error("Snapshot tidak ditemukan: {$path}");

            return self::FAILURE;
        }

        try {
            $compressed = file_get_contents($path);
            $json = $compressed === false ? false : gzdecode($compressed);
            if ($json === false) {
                throw new RuntimeException('Snapshot bukan gzip yang valid.');
            }
            $payload = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
            if (($payload['format'] ?? null) !== 'smk-alfatih-portable-data' || ($payload['version'] ?? null) !== 1 || ! is_array($payload['tables'] ?? null)) {
                throw new RuntimeException('Format snapshot tidak dikenali.');
            }

            $tables = $payload['tables'];
            foreach (array_keys($tables) as $table) {
                if (! preg_match('/^[a-z0-9_]+$/', $table) || ! Schema::hasTable($table) || ! is_array($tables[$table])) {
                    throw new RuntimeException("Tabel snapshot tidak valid atau skema lokal belum siap: {$table}");
                }
            }

            Schema::disableForeignKeyConstraints();
            try {
                DB::transaction(function () use ($tables): void {
                    foreach (array_reverse(array_keys($tables)) as $table) {
                        DB::table($table)->delete();
                    }
                    foreach ($tables as $table => $rows) {
                        foreach (array_chunk($rows, 250) as $chunk) {
                            if ($chunk !== []) {
                                DB::table($table)->insert($chunk);
                            }
                        }
                        $this->line("{$table}: ".count($rows));
                    }
                });
            } finally {
                Schema::enableForeignKeyConstraints();
            }

            $this->resetSqliteSequences(array_keys($tables));
            $this->info('Data lokal berhasil disamakan dari snapshot.');

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Import dibatalkan: '.$e->getMessage());

            return self::FAILURE;
        }
    }

    /** @param array<int, string> $tables */
    private function resetSqliteSequences(array $tables): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite' || ! Schema::hasTable('sqlite_sequence')) {
            return;
        }

        foreach ($tables as $table) {
            if (! Schema::hasColumn($table, 'id')) {
                continue;
            }
            $maximum = (int) DB::table($table)->max('id');
            DB::table('sqlite_sequence')->where('name', $table)->delete();
            if ($maximum > 0) {
                DB::table('sqlite_sequence')->insert(['name' => $table, 'seq' => $maximum]);
            }
        }
    }

    private function absolutePath(string $path): string
    {
        if (preg_match('/^(?:[A-Za-z]:[\\\\\/]|\/)/', $path) === 1) {
            return $path;
        }

        return base_path($path);
    }
}
