<?php

namespace App\Actions\Backups;

use Spatie\Backup\BackupDestination\Backup;
use Spatie\Backup\BackupDestination\BackupDestination;
use Spatie\Backup\BackupDestination\BackupDestinationFactory;
use Throwable;

class ListBackups
{
    /**
     * List every backup found on the configured destination disks.
     *
     * @return array<int, array<string, mixed>>
     */
    public function handle(): array
    {
        $backups = [];

        try {
            $destinations = BackupDestinationFactory::createFromArray(config('backup.backup'));
        } catch (Throwable) {
            return $backups;
        }

        /** @var BackupDestination $destination */
        foreach ($destinations as $destination) {
            foreach ($destination->backups() as $backup) {
                /** @var Backup $backup */
                $backups[] = [
                    'filename' => basename($backup->path()),
                    'disk' => $destination->diskName(),
                    'size' => $backup->sizeInBytes(),
                    'size_human' => $this->formatBytes($backup->sizeInBytes()),
                    'date' => $backup->date()->toIso8601String(),
                ];
            }
        }

        usort($backups, fn (array $a, array $b) => strcmp($b['date'], $a['date']));

        return $backups;
    }

    /**
     * Format a byte count into a human-readable string.
     */
    protected function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = (int) floor(log($bytes, 1024));

        return round($bytes / (1024 ** $power), 2).' '.$units[$power];
    }
}
