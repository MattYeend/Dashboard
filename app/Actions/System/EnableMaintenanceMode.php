<?php

namespace App\Actions\System;

use Illuminate\Support\Facades\Artisan;

class EnableMaintenanceMode
{
    /**
     * Put the application into maintenance mode.
     *
     * @param  array<string, mixed>  $options  Optional secret, retry, refresh, and allowed IPs.
     */
    public function handle(array $options = []): void
    {
        Artisan::call('down', array_filter([
            '--secret' => $options['secret'] ?? null,
            '--retry' => $options['retry'] ?? null,
            '--refresh' => $options['refresh'] ?? null,
            '--allow' => $options['allowed'] ?? null,
        ], static fn ($value) => $value !== null));
    }
}
