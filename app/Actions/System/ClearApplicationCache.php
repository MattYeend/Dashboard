<?php

namespace App\Actions\System;

use Illuminate\Support\Facades\Artisan;

class ClearApplicationCache
{
    /**
     * Clear the application cache via artisan.
     */
    public function handle(): void
    {
        Artisan::call('cache:clear');
    }
}
