<?php

namespace App\Services\Organisations;

use App\Models\Organisation;
use App\Models\OrganisationMembership;
use App\Models\User;
use App\Services\UserRoleCheckerService;

class PolicyAuthorisationService
{
    /**
     * Inject the required services into the policy authorisation service.
     */
    public function __construct(
        protected readonly ActiveCheckerService $activeChecker,
        protected readonly UserRoleCheckerService $roleChecker
    ) {}

    /**
     * Check if user is admin or super admin.
     */
    public function isAdmin(User $user): bool
    {
        return $this->roleChecker->isAdmin($user);
    }

    /**
     * Determine whether the user can view any organisations.
     */
    public function canViewAny(User $actor): bool
    {
        return $actor->can('view any organisations');
    }

    /**
     * Determine whether the user can create organisations.
     */
    public function canCreate(User $actor): bool
    {
        return $actor->can('create organisations');
    }

    /**
     * Determine whether the user can view the organisation.
     */
    public function canView(User $actor, Organisation $target): bool
    {
        return $actor->can('view organisations')
            && $this->activeChecker->isActive($target)
            && $this->isActiveMember($actor, $target);
    }

    /**
     * Determine whether the user can update the organisation.
     */
    public function canUpdate(User $actor, Organisation $target): bool
    {
        return $actor->can('edit organisations')
            && $this->activeChecker->isActive($target)
            && $this->isActiveMember($actor, $target);
    }

    /**
     * Determine whether the user can delete the organisation.
     */
    public function canDelete(User $actor, Organisation $target): bool
    {
        return $actor->can('delete organisations')
            && $this->activeChecker->canBeModified($target);
    }

    /**
     * Determine whether the user can restore the organisation.
     */
    public function canRestore(User $actor, Organisation $target): bool
    {
        return $actor->can('restore organisations')
            && $this->activeChecker->canBeRestoredOrForceDeleted($target);
    }

    /**
     * Determine whether the user can permanently delete the organisation.
     */
    public function canForceDelete(User $actor, Organisation $target): bool
    {
        return $this->activeChecker->canUserPerformAction($actor, 'restoreOrForceDelete', $target);
    }

    /**
     * Determine whether the actor can view billing for the given organisation.
     *
     * Requires active membership first — admin rank alone does not grant
     * billing visibility into an organisation the actor doesn't belong to.
     */
    public function canViewBilling(User $actor, Organisation $organisation): bool
    {
        return $this->isActiveMember($actor, $organisation)
            && ($this->isAdmin($actor) || $organisation->hasBillingRoleFor($actor));
    }

    /**
     * Determine whether the actor can manage billing for the given organisation.
     */
    public function canManageBilling(User $actor, Organisation $organisation): bool
    {
        return $this->canViewBilling($actor, $organisation);
    }

    /**
     * Determine whether the user can switch into the organisation.
     */
    public function canSwitch(User $actor, Organisation $target): bool
    {
        return $this->activeChecker->isActive($target)
            && $this->isActiveMember($actor, $target);
    }

    /**
     * Determine whether the user can remove a member from the organisation.
     */
    public function canRemoveMember(User $actor, Organisation $target): bool
    {
        return $actor->can('remove organisation members')
            && $this->activeChecker->isActive($target)
            && $this->isActiveMember($actor, $target);
    }

    /**
     * Determine whether the actor holds an active membership in the
     * given organisation.
     *
     * Super admin rank does not bypass this check - organisation
     * membership is a hard boundary, independent of role rank.
     */
    private function isActiveMember(User $actor, Organisation $target): bool
    {
        return $target->users()
            ->wherePivot('status', OrganisationMembership::STATUS_ACTIVE)
            ->whereKey($actor->id)
            ->exists();
    }
}
