<?php

namespace App\Actions\System;

use Illuminate\Support\Facades\DB;
use Throwable;

class GetSystemInfo
{
    /**
     * Gather a snapshot of the current system state.
     *
     * @return array<string, mixed>
     */
    public function handle(): array
    {
        return [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'environment' => app()->environment(),
            'debug_mode' => (bool) config('app.debug'),
            'maintenance_mode' => app()->isDownForMaintenance(),
            'database_connection' => $this->checkDatabaseConnection(),
            'cache_driver' => config('cache.default'),
            'queue_driver' => config('queue.default'),
            'disk_free_space' => $this->formatBytes(disk_free_space(base_path())),
            'disk_total_space' => $this->formatBytes(disk_total_space(base_path())),
            'server_time' => now()->toDateTimeString(),
        ];
    }

    /**
     * Check whether the default database connection is reachable.
     */
    protected function checkDatabaseConnection(): string
    {
        try {
            DB::connection()->getPdo();

            return 'connected';
        } catch (Throwable) {
            return 'disconnected';
        }
    }

    /**
     * Format a byte count into a human-readable string.
     */
    protected function formatBytes(int|false $bytes): string
    {
        if ($bytes === false) {
            return 'unknown';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = $bytes > 0 ? (int) floor(log($bytes, 1024)) : 0;

        return round($bytes / (1024 ** $power), 2).' '.$units[$power];
    }
}
