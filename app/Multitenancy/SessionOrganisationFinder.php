<?php

namespace App\Multitenancy;

use App\Models\Organisation;
use App\Models\OrganisationMembership;
use App\Services\UserRoleCheckerService;
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
 *
 * Super admins are not required to hold an `organisation_user` pivot row
 * to switch into or resolve any organisation, mirroring the bypass in
 * PolicyAuthorisationService::isMemberOrSuperAdmin().
 */
class SessionOrganisationFinder extends TenantFinder
{
    public function __construct(
        protected readonly UserRoleCheckerService $roleChecker
    ) {}

    /**
     * Find the current tenant (organisation) for the given request.
     */
    public function findForRequest(Request $request): ?IsTenant
    {
        $user = $request->user();

        if ($user === null) {
            return null;
        }

        $isSuperAdmin = $this->roleChecker->isSuperAdmin($user);
        $organisationId = $request->session()->get('current_organisation_id');

        if ($organisationId !== null) {
            $organisation = app(IsTenant::class)::query()
                ->whereKey($organisationId)
                ->when(! $isSuperAdmin, fn ($q) => $q->whereHas(
                    'users',
                    fn ($q2) => $q2->whereKey($user->id)
                        ->wherePivot('status', OrganisationMembership::STATUS_ACTIVE)
                ))
                ->first();

            if ($organisation !== null) {
                return $organisation;
            }

            $request->session()->forget('current_organisation_id');
        }

        $organisation = $isSuperAdmin
            ? app(IsTenant::class)::query()->oldest('id')->first()
            : $user->activeOrganisations()->oldest('organisation_user.id')->first();

        if ($organisation !== null) {
            $request->session()->put('current_organisation_id', $organisation->id);
        }

        return $organisation;
    }
}
