<?php

namespace App\Actions\Backups;

use App\Exceptions\BackupNotFoundException;
use Spatie\Backup\BackupDestination\BackupDestinationFactory;

class DeleteBackup
{
    /**
     * Delete a single backup by filename from the given disk.
     *
     * The filename is re-sanitised here (basename only) even though the
     * route parameter is already constrained by regex — defence in depth
     * against path traversal.
     */
    public function handle(string $disk, string $filename): void
    {
        $filename = basename($filename);

        $destination = BackupDestinationFactory::createFromArray(
            config('backup.backup'),
            $disk
        );

        $backup = $destination->backups()->first(
            fn ($backup) => basename($backup->path()) === $filename
        );

        if ($backup === null) {
            throw new BackupNotFoundException("Backup [{$filename}] was not found on disk [{$disk}].");
        }

        $backup->delete();
    }
}
