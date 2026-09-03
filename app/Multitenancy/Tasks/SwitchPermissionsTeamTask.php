<?php

namespace App\Multitenancy\Tasks;

use App\Models\Organisation;
use Spatie\Multitenancy\Contracts\IsTenant;
use Spatie\Multitenancy\Tasks\SwitchTenantTask;
use Spatie\Permission\PermissionRegistrar;

/**
 * Scopes spatie/laravel-permission role and permission checks to the
 * current organisation by setting the active permissions "team" id
 * whenever the tenant is switched.
 */
class SwitchPermissionsTeamTask implements SwitchTenantTask
{
    /**
     * Set the permissions team id to the organisation being made current.
     */
    public function makeCurrent(IsTenant $tenant): void
    {
        /** @var Organisation $tenant */
        app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->id);
    }

    /**
     * Clear the permissions team id when forgetting the current tenant.
     */
    public function forgetCurrent(): void
    {
        app(PermissionRegistrar::class)->setPermissionsTeamId(null);
    }
}
