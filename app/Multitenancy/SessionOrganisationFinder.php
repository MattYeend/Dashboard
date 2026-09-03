<?php

namespace App\Multitenancy;

use Illuminate\Http\Request;
use Spatie\Multitenancy\Models\Concerns\UsesTenantModel;
use Spatie\Multitenancy\Models\Tenant;
use Spatie\Multitenancy\TenantFinder\TenantFinder;

class SessionOrganisationFinder extends TenantFinder
{
    use UsesTenantModel;

    /**
     * Find the current tenant (organisation) for the given request.
     */
    public function findForRequest(Request $request): ?Tenant
    {
        $user = $request->user();

        if ($user === null) {
            return null;
        }

        $organisationId = $request->session()->get('current_organisation_id');

        if ($organisationId !== null) {
            $organisation = $this->getTenantModel()::query()
                ->whereKey($organisationId)
                ->whereHas('users', fn ($query) => $query->whereKey($user->id))
                ->first();

            if ($organisation !== null) {
                return $organisation;
            }

            // Session pointed at an organisation the user no longer belongs
            // to (or that's been deleted); clear it and fall through.
            $request->session()->forget('current_organisation_id');
        }

        $organisation = $user->organisations()->oldest('organisation_user.id')->first();

        if ($organisation !== null) {
            $request->session()->put('current_organisation_id', $organisation->id);
        }

        return $organisation;
    }
}
