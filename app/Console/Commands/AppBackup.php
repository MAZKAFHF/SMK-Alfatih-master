<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AppBackup extends Command
{
    protected $signature = 'app:backup {--retention= : Keep N backup sets; defaults to config}';
    protected $description = 'Create a verified database, public media, and private PPDB document backup';

    public function handle(): int
    {
        $retention = max(1, (int) ($this->option('retention') ?: config('backup.retention', 14)));
        $timestamp = now()->format('Ymd_His');
        // Unit tests must never create or prune operational backups.
        $backupDir = app()->runningUnitTests()
            ? storage_path('framework/testing/backups')
            : (string) config('backup.directory', storage_path('app/backups'));
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
            } elseif ($db === 'pgsql') {
                $host = $connection['host'] ?? '127.0.0.1';
                $port = $connection['port'] ?? 5432;
                $database = $connection['database'] ?? '';
                $username = $connection['username'] ?? '';
                $password = $connection['password'] ?? '';
                $plainDest = "{$backupDir}/db-{$timestamp}.sql";
                $dest = "{$plainDest}.gz";
                $cmd = sprintf(
                    'pg_dump --host=%s --port=%s --username=%s --dbname=%s --no-owner --no-privileges --file=%s',
                    escapeshellarg($host),
                    escapeshellarg((string) $port),
                    escapeshellarg($username),
                    escapeshellarg($database),
                    escapeshellarg($plainDest)
                );
                $this->info('Running pg_dump...');
                if ($password !== '') {
                    putenv('PGPASSWORD='.$password);
                }
                passthru($cmd, $ret);
                putenv('PGPASSWORD');
                if ($ret !== 0 || ! is_file($plainDest) || filesize($plainDest) === 0) {
                    @unlink($plainDest);
                    $this->error("pg_dump failed (code {$ret})");
                    return self::FAILURE;
                }
                $sql = file_get_contents($plainDest);
                if ($sql === false || file_put_contents($dest, gzencode($sql, 9)) === false) {
                    @unlink($plainDest);
                    @unlink($dest);
                    $this->error('PostgreSQL backup compression failed.');
                    return self::FAILURE;
                }
                @unlink($plainDest);
                $this->info("DB dumped to {$dest}");
            } elseif ($db === 'mysql') {
                $host = $connection['host'] ?? '127.0.0.1';
                $port = $connection['port'] ?? 3306;
                $database = $connection['database'] ?? '';
                $username = $connection['username'] ?? '';
                $password = $connection['password'] ?? '';
                $dest = "{$backupDir}/db-{$timestamp}.sql.gz";
                $cmd = sprintf(
                    'mysqldump -h %s -P %s -u %s %s | gzip > %s',
                    escapeshellarg($host),
                    escapeshellarg((string) $port),
                    escapeshellarg($username),
                    escapeshellarg($database),
                    escapeshellarg($dest)
                );
                $this->info('Running mysqldump...');
                if ($password !== '') {
                    putenv('MYSQL_PWD='.$password);
                }
                passthru($cmd, $ret);
                putenv('MYSQL_PWD');
                if ($ret !== 0 || ! file_exists($dest)) {
                    $this->error("mysqldump failed (code {$ret})");
                    return self::FAILURE;
                }
                $this->info("DB dumped to {$dest}");
            } else {
                $this->error("Unsupported database driver: {$db}");
                return self::FAILURE;
            }

            $filesDest = "{$backupDir}/files-{$timestamp}.tar";
            $archive = new \PharData($filesDest);
            $fileCount = 0;
            foreach (['public' => storage_path('app/public'), 'private/ppdb' => storage_path('app/private/ppdb')] as $prefix => $source) {
                if (! is_dir($source)) {
                    continue;
                }
                $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($source, \FilesystemIterator::SKIP_DOTS));
                foreach ($iterator as $file) {
                    if ($file->isFile()) {
                        $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($source) + 1));
                        $archive->addFile($file->getPathname(), "{$prefix}/{$relative}");
                        $fileCount++;
                    }
                }
            }
            $archive->compress(\Phar::GZ);
            unset($archive);
            @unlink($filesDest);
            $this->info("Public media + private PPDB documents archived ({$fileCount} files): {$filesDest}.gz");

            $backupFiles = array_values(array_filter(glob("{$backupDir}/*-{$timestamp}*") ?: [], fn ($file) => ! str_ends_with($file, '.json')));
            $manifest = [
                'version' => 1,
                'created_at' => now()->toIso8601String(),
                'database_driver' => $db,
                'files' => array_map(fn ($file) => [
                    'name' => basename($file), 'bytes' => filesize($file), 'sha256' => hash_file('sha256', $file),
                ], $backupFiles),
            ];
            file_put_contents("{$backupDir}/manifest-{$timestamp}.json", json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            $this->prune($backupDir, 'db-*', $retention);
            $this->prune($backupDir, 'files-*', $retention);
            $this->prune($backupDir, 'manifest-*', $retention);

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

        // A SQLite backup has both .sqlite and .sqlite.gz files. Retention is
        // counted per timestamped backup set, not per individual file.
        $sets = [];
        foreach ($files as $file) {
            if (preg_match('/^(?:db|files|manifest)-(\d{8}_\d{6})/', basename($file), $matches)) {
                $sets[$matches[1]][] = $file;
            }
        }
        krsort($sets);

        foreach (array_slice($sets, $keep, null, true) as $oldSet) {
            foreach ($oldSet as $old) {
                @unlink($old);
                $this->info('Pruned old backup: '.basename($old));
            }
        }
    }
}
