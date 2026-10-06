<?php

namespace App\Rules;

use App\Models\OrganisationMembership;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;
use Spatie\Multitenancy\Models\Tenant;

class TenantRules
{
    /**
     * The row must exist in a tenant-scoped table, belong to the current
     * organisation and not be soft-deleted. Fails closed with no tenant.
     */
    public static function exists(string $table): Exists
    {
        return Rule::exists($table, 'id')
            ->where('organisation_id', Tenant::current()?->id)
            ->whereNull('deleted_at');
    }

    /**
     * The user must be an active member of the current organisation.
     */
    public static function activeMember(): Exists
    {
        return Rule::exists('organisation_user', 'user_id')
            ->where('organisation_id', Tenant::current()?->id)
            ->where('status', OrganisationMembership::STATUS_ACTIVE);
    }
}
