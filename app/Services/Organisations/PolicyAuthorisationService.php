<?php

namespace App\Services\Organisations;

use App\Models\Organisation;
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
            && $this->isMemberOrSuperAdmin($actor, $target);
    }

    /**
     * Determine whether the user can update the organisation.
     */
    public function canUpdate(User $actor, Organisation $target): bool
    {
        return $actor->can('edit organisations')
            && $this->activeChecker->isActive($target)
            && $this->isMemberOrSuperAdmin($actor, $target);
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
     * Determine whether the actor is a member of the organisation, or a
     * super admin who can view organisations they don't belong to.
     */
    private function isMemberOrSuperAdmin(User $actor, Organisation $target): bool
    {
        if ($this->roleChecker->isSuperAdmin($actor)) {
            return true;
        }

        return $target->users()->whereKey($actor->id)->exists();
    }
}
