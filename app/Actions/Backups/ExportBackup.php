<?php

namespace App\Actions\Backups;

use App\Exceptions\BackupNotFoundException;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Spatie\Backup\BackupDestination\BackupDestination;
use Spatie\Backup\BackupDestination\BackupDestinationFactory;
use Spatie\Backup\Config\Config;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportBackup
{
    public function __construct(protected Config $config) {}

    /**
     * Stream a backup file to the browser as a download.
     */
    public function handle(string $disk, string $filename): StreamedResponse
    {
        $filename = basename($filename);

        $destination = BackupDestinationFactory::createFromArray(config('backup.backup'))
            ->first(fn (BackupDestination $destination) => $destination->diskName() === $disk);

        if ($destination === null) {
            throw new BackupNotFoundException("Disk [{$disk}] is not a configured backup destination.");
        }

        $backup = $destination->backups()->first(
            fn ($backup) => basename($backup->path()) === $filename
        );

        if ($backup === null) {
            throw new BackupNotFoundException("Backup [{$filename}] was not found on disk [{$disk}].");
        }

        /** @var FilesystemAdapter $storageDisk */
        $storageDisk = Storage::disk($disk);

        return $storageDisk->download($backup->path(), $filename);
    }
}
