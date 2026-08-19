<?php

namespace App\Actions\System;

use Illuminate\Support\Facades\Artisan;

class DisableMaintenanceMode
{
    /**
     * Bring the application back up out of maintenance mode.
     */
    public function handle(): void
    {
        Artisan::call('up');
    }
}
