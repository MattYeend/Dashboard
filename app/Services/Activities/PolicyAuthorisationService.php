<?php

namespace App\Services\Activities;

use App\Models\Activity;
use App\Models\User;
use App\Services\UserRoleCheckerService;

class PolicyAuthorisationService
{
    /**
     * Inject the required services into the policy authorisation service.
     */
    public function __construct(
        protected readonly ActiveCheckerService $activeChecker,
        protected readonly UserRoleCheckerService $roleChecker,
    ) {}

    /**
     * Check if user is a regular user, admin, or super admin.
     */
    public function isUser(User $user): bool
    {
        return $this->roleChecker->isUser($user);
    }

    /**
     * Check if user is admin or super admin.
     */
    public function isAdmin(User $user): bool
    {
        return $this->roleChecker->isAdmin($user);
    }

    /**
     * Check if activity is active (not soft-deleted).
     */
    public function isActive(Activity $activity): bool
    {
        return $this->activeChecker->isActive($activity);
    }

    /**
     * Check if activity is soft-deleted.
     */
    public function isTrashed(Activity $activity): bool
    {
        return $this->activeChecker->isTrashed($activity);
    }

    /**
     * Determine whether the user can view any activities.
     */
    public function canViewAny(User $actor): bool
    {
        return $actor->can('view any activities');
    }

    /**
     * Determine whether the user can create activities.
     */
    public function canCreate(User $actor): bool
    {
        return $actor->can('create activities');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function canView(User $actor, Activity $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('view activities') && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function canUpdate(User $actor, Activity $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('edit activities') && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function canDelete(User $actor, Activity $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('delete activities') && $this->activeChecker->canBeModified($target);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function canRestore(User $actor, Activity $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('restore activities')
            && $this->activeChecker->canBeRestoredOrForceDeleted($target);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function canForceDelete(User $actor, Activity $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $this->activeChecker->canUserPerformAction($actor, 'restoreOrForceDelete', $target);
    }

    /**
     * Determine whether the user can export activities.
     */
    public function canExport(User $actor, ?int $scopedActivityableId = null): bool
    {
        if ($scopedActivityableId !== null) {
            return $actor->can('export activities');
        }

        // Unscoped, cross-record export is admin-only.
        return $this->roleChecker->isAdmin($actor) && $actor->can('export activities');
    }

    /**
     * Activities aren't owned by a User the way Addresses/Contacts are, so
     * this checks the ranking of whoever *logged* the entry rather than an
     * "owner" relation — a lower-ranked user can't delete/restore an entry
     * a super admin recorded.
     */
    private function targetOutranksActor(User $actor, Activity $target): bool
    {
        if ($this->roleChecker->isSuperAdmin($actor)) {
            return false;
        }

        return $target->creator instanceof User && $this->roleChecker->isSuperAdmin($target->creator);
    }
}
