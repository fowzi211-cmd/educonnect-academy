<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use ZipArchive;

/**
 * Archives everything a restore actually needs — the SQLite database plus
 * every uploaded file (receipts, lecturer documents, course/news images,
 * logos) — into one timestamped zip. Uses PHP's own ZipArchive rather than
 * shelling out to a "zip" binary, so it works the same on any host without
 * assuming what's installed.
 *
 * Only backs up the SQLite file directly; a MySQL/Postgres deployment
 * should back up its database with that engine's own dump tool instead —
 * this command still archives the uploaded-file storage either way.
 */
#[Signature('app:backup {--keep=14 : Delete backups older than this many days}')]
#[Description('Back up the SQLite database and uploaded files into storage/backups')]
class BackupCommand extends Command
{
    public function handle(): int
    {
        $backupDir = storage_path('backups');
        File::ensureDirectoryExists($backupDir);

        $timestamp = now()->format('Y-m-d_His');
        $zipPath = "{$backupDir}/backup_{$timestamp}.zip";

        $zip = new ZipArchive;

        if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
            $this->error("Could not create archive at {$zipPath}.");

            return self::FAILURE;
        }

        if (config('database.default') === 'sqlite') {
            $dbPath = config('database.connections.sqlite.database');

            if ($dbPath && $dbPath !== ':memory:' && File::exists($dbPath)) {
                $zip->addFile($dbPath, 'database.sqlite');
                $this->info('Added database.sqlite.');
            } else {
                $this->warn('SQLite database file not found — skipping.');
            }
        } else {
            $this->warn('DB_CONNECTION is not sqlite — back up your database separately with its own dump tool (e.g. mysqldump). Continuing with file storage only.');
        }

        $fileCount = 0;
        foreach (['app/private' => 'storage/private', 'app/public' => 'storage/public'] as $relative => $zipFolder) {
            $dir = storage_path($relative);

            if (! File::isDirectory($dir)) {
                continue;
            }

            foreach (File::allFiles($dir) as $file) {
                // Livewire's own temp-upload staging area — always transient
                // (files are moved out or garbage-collected), never worth restoring.
                $relativePath = str_replace('\\', '/', $file->getRelativePathname());

                if (str_starts_with($relativePath, 'livewire-tmp/')) {
                    continue;
                }

                // Zip entries must use "/" regardless of host OS, or archive
                // tools on a different platform than this one misread them.
                $zip->addFile($file->getPathname(), $zipFolder.'/'.$relativePath);
                $fileCount++;
            }
        }

        $zip->close();

        $this->info("Archived {$fileCount} uploaded file(s).");
        $this->info('Backup written to '.$zipPath.' ('.$this->humanSize(File::size($zipPath)).').');

        $this->pruneOldBackups($backupDir, (int) $this->option('keep'));

        return self::SUCCESS;
    }

    protected function pruneOldBackups(string $backupDir, int $keepDays): void
    {
        $cutoff = now()->subDays($keepDays);
        $removed = 0;

        foreach (File::files($backupDir) as $file) {
            if (now()->createFromTimestamp($file->getMTime())->lt($cutoff)) {
                File::delete($file->getPathname());
                $removed++;
            }
        }

        if ($removed > 0) {
            $this->info("Removed {$removed} backup(s) older than {$keepDays} days.");
        }
    }

    protected function humanSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 1).' '.$units[$i];
    }
}
