<?php

namespace App\Actions\Backups;

use Illuminate\Support\Facades\Artisan;

class CreateBackup
{
    /**
     * Trigger a new backup run via the spatie/laravel-backup artisan command.
     */
    public function handle(bool $onlyDb = false): void
    {
        Artisan::call('backup:run', array_filter([
            '--only-db' => $onlyDb ?: null,
        ]));
    }
}
