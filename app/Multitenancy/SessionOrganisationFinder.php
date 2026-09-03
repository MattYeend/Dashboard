<?php

namespace App\Multitenancy;

use App\Models\Organisation;
use Illuminate\Http\Request;
use Spatie\Multitenancy\Contracts\IsTenant;
use Spatie\Multitenancy\TenantFinder\TenantFinder;

/**
 * Resolves the current organisation from the logged-in user's session
 * rather than from the request's domain or subdomain.
 *
 * Falls back to the user's first (or only) organisation membership when
 * no organisation id is present on the session, and persists that choice
 * back to the session so subsequent requests resolve consistently.
 */
class SessionOrganisationFinder extends TenantFinder
{
    /**
     * Find the current tenant (organisation) for the given request.
     */
    public function findForRequest(Request $request): ?IsTenant
    {
        $user = $request->user();

        if ($user === null) {
            return null;
        }

        $organisationId = $request->session()->get('current_organisation_id');

        if ($organisationId !== null) {
            /** @var Organisation|null $organisation */
            $organisation = app(IsTenant::class)::query()
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
