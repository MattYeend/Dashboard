<?php

namespace Database\Seeders\Concerns;

use App\Models\Organisation;

/**
 * Resolves (or creates) the Default organisation used to backfill
 * seeder-created records that predate multi-organisation support,
 * and makes it the current tenant so BelongsToOrganisation-guarded
 * models can be created without every seeder call site needing to
 * remember to do so itself.
 */
trait ResolvesDefaultOrganisation
{
    protected function defaultOrganisation(): Organisation
    {
        $organisation = Organisation::firstOrCreate(
            ['slug' => 'default'],
            ['name' => 'Default', 'slug' => 'default']
        );

        $organisation->makeCurrent();

        return $organisation;
    }
}
