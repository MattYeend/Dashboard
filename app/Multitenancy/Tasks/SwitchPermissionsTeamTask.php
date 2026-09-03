<?php

namespace App\Multitenancy\Tasks;

use Spatie\Multitenancy\Contracts\IsTenant;
use Spatie\Multitenancy\Tasks\SwitchTenantTask;
use Spatie\Permission\PermissionRegistrar;

class SwitchPermissionsTeamTask implements SwitchTenantTask
{
    /**
     * Set the permissions team id to the organisation being made current.
     */
    public function makeCurrent(IsTenant $tenant): void
    {
        app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->getKey());
    }

    /**
     * Clear the permissions team id when forgetting the current tenant.
     */
    public function forgetCurrent(IsTenant $tenant): void
    {
        app(PermissionRegistrar::class)->setPermissionsTeamId(null);
    }
}
