<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class VerifyBackup extends Command
{
    protected $signature = 'app:backup-verify {manifest? : Manifest filename or absolute path; latest when omitted}';
    protected $description = 'Verify backup-set checksums without modifying application data';

    public function handle(): int
    {
        $directory = app()->runningUnitTests() ? storage_path('framework/testing/backups') : (string) config('backup.directory', storage_path('app/backups'));
        $requested = $this->argument('manifest');
        $path = $requested
            ? (str_contains((string) $requested, '/') || str_contains((string) $requested, '\\') ? (string) $requested : $directory.DIRECTORY_SEPARATOR.$requested)
            : collect(glob($directory.DIRECTORY_SEPARATOR.'manifest-*.json') ?: [])->sortDesc()->first();

        if (! $path || ! is_file($path)) {
            $this->error('Manifest backup tidak ditemukan.');
            return self::FAILURE;
        }
        $manifest = json_decode((string) file_get_contents($path), true);
        if (! is_array($manifest) || empty($manifest['files'])) {
            $this->error('Manifest backup kosong atau tidak valid.');
            return self::FAILURE;
        }
        foreach ($manifest['files'] as $entry) {
            $file = dirname($path).DIRECTORY_SEPARATOR.basename((string) ($entry['name'] ?? ''));
            if (! is_file($file) || ! hash_equals((string) ($entry['sha256'] ?? ''), hash_file('sha256', $file))) {
                $this->error('Backup hilang atau checksum tidak cocok: '.basename($file));
                return self::FAILURE;
            }
        }
        $this->info('Backup terverifikasi: '.basename($path).' ('.count($manifest['files']).' berkas).');
        return self::SUCCESS;
    }
}
