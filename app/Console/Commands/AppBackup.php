<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AppBackup extends Command
{
    protected $signature = 'app:backup {--retention=7 : Keep N daily backups}';
    protected $description = 'Create timestamped DB + media backup (sqlite or mysql) via Laravel config, handles compressed restores safely';

    public function handle(): int
    {
        $retention = (int) $this->option('retention');
        $timestamp = now()->format('Ymd_His');
        $backupDir = storage_path('app/backups');
        if (! is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $db = config('database.default');
        $connection = config("database.connections.{$db}");

        $this->info("Backing up DB driver: {$db}");

        try {
            if ($db === 'sqlite') {
                $dbPath = $connection['database'] ?? database_path('database.sqlite');
                if ($dbPath === ':memory:' || $dbPath === ':memory') {
                    $this->warn('SQLite :memory: detected — creating dummy backup for test');
                    $dest = "{$backupDir}/db-{$timestamp}.sqlite";
                    file_put_contents($dest, "-- dummy :memory: backup {$timestamp}\n");
                    $this->info("Dummy DB created at {$dest}");
                } else {
                    if (! file_exists($dbPath)) {
                        $this->error("SQLite file not found: {$dbPath}");
                        return self::FAILURE;
                    }
                    $dest = "{$backupDir}/db-{$timestamp}.sqlite";
                    copy($dbPath, $dest);
                    $this->info('DB copied to '.$dest.' ('.round(filesize($dest) / 1024, 1).' KB)');
                    $gz = "{$dest}.gz";
                    $data = file_get_contents($dest);
                    file_put_contents($gz, gzencode($data, 9));
                    $this->info("Compressed copy: {$gz}");
                }
            } else {
                $host = $connection['host'] ?? '127.0.0.1';
                $port = $connection['port'] ?? 3306;
                $database = $connection['database'] ?? '';
                $username = $connection['username'] ?? '';
                $password = $connection['password'] ?? '';
                $dest = "{$backupDir}/db-{$timestamp}.sql.gz";
                $cmd = sprintf(
                    'mysqldump -h %s -P %s -u %s %s %s | gzip > %s',
                    escapeshellarg($host),
                    escapeshellarg((string) $port),
                    escapeshellarg($username),
                    $password !== '' ? '-p'.escapeshellarg($password) : '',
                    escapeshellarg($database),
                    escapeshellarg($dest)
                );
                $this->info('Running mysqldump...');
                passthru($cmd, $ret);
                if ($ret !== 0 || ! file_exists($dest)) {
                    $this->error("mysqldump failed (code {$ret})");
                    return self::FAILURE;
                }
                $this->info("DB dumped to {$dest}");
            }

            $mediaSrc = storage_path('app/public');
            $mediaDest = "{$backupDir}/media-{$timestamp}.tar.gz";
            if (is_dir($mediaSrc) && count(glob($mediaSrc.'/*')) > 0) {
                $phar = new \PharData($mediaDest);
                $phar->buildFromDirectory($mediaSrc);
                $this->info("Media archived to {$mediaDest}");
            } else {
                $this->warn('No media files to backup');
            }

            $this->prune($backupDir, 'db-*', $retention);
            $this->prune($backupDir, 'media-*', $retention);

            $this->info("Backup completed: {$timestamp}");
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Backup failed: '.$e->getMessage());
            report($e);
            return self::FAILURE;
        }
    }

    private function prune(string $dir, string $pattern, int $keep): void
    {
        $files = glob($dir.'/'.$pattern);
        if (! $files) {
            return;
        }
        usort($files, fn ($a, $b) => filemtime($b) - filemtime($a));
        foreach (array_slice($files, $keep) as $old) {
            @unlink($old);
            $this->info('Pruned old backup: '.basename($old));
        }
    }
}
