<?php

namespace App\Actions\Backups;

use Illuminate\Support\Facades\Log;
use Spatie\Backup\BackupDestination\Backup;
use Spatie\Backup\Config\Config;
use Spatie\Backup\Tasks\Monitor\BackupDestinationStatus;
use Spatie\Backup\Tasks\Monitor\BackupDestinationStatusFactory;
use Throwable;

class ListBackups
{
    public function __construct(protected Config $config) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function handle(): array
    {
        $backups = [];

        try {
            $statuses = BackupDestinationStatusFactory::createForMonitorConfig($this->config->monitoredBackups);

            /** @var BackupDestinationStatus $status */
            foreach ($statuses as $status) {
                $destination = $status->backupDestination();

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
        } catch (Throwable $exception) {
            Log::warning('Failed to list backups.', [
                'exception' => $exception->getMessage(),
            ]);

            return $backups;
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
