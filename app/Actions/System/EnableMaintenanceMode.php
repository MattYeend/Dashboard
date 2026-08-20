<?php

namespace App\Actions\System;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class EnableMaintenanceMode
{
    /**
     * Put the application into maintenance mode.
     *
     * Always generates a secret bypass token (unless one is supplied),
     * since Laravel's `down` command has no IP allow-list option - the
     * secret URL is the only way back in until `up` runs.
     *
     * @param  array<string, mixed>  $options
     * @return string The secret bypass token.
     */
    public function handle(array $options = []): string
    {
        $secret = $options['secret'] ?? Str::random(40);

        Artisan::call('down', array_filter([
            '--secret' => $secret,
            '--retry' => $options['retry'] ?? null,
            '--refresh' => $options['refresh'] ?? null,
        ], static fn ($value) => $value !== null));

        return $secret;
    }
}
