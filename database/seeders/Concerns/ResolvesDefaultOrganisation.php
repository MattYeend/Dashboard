<?php

namespace Database\Seeders\Concerns;

use App\Models\Organisation;

/**
 * Resolves (or creates) the Default organisation used to backfill
 * seeder-created records that predate multi-organisation support.
 */
trait ResolvesDefaultOrganisation
{
    /**
     * Get the Default organisation, creating it if it doesn't exist yet.
     */
    protected function defaultOrganisation(): Organisation
    {
        return Organisation::firstOrCreate(
            ['slug' => 'default'],
            ['name' => 'Default', 'slug' => 'default']
        );
    }
}
