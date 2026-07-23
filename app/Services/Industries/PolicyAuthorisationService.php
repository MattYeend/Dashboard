<?php

namespace App\Services\Industries;

use App\Models\Industry;
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
     * Check if user is a regular user, admin, or super admin.
     */
    public function isUser(
        User $user
    ): bool {
        return $this->roleChecker->isUser(
            $user
        );
    }

    /**
     * Check if user is admin or super admin.
     */
    public function isAdmin(
        User $user
    ): bool {
        return $this->roleChecker->isAdmin(
            $user
        );
    }

    /**
     * Check if industry is active (not soft-deleted).
     */
    public function isActive(
        Industry $industry
    ): bool {
        return $this->activeChecker->isActive(
            $industry
        );
    }

    /**
     * Check if industry is soft-deleted.
     */
    public function isTrashed(
        Industry $industry
    ): bool {
        return $this->activeChecker->isTrashed(
            $industry
        );
    }

    /**
     * Determine whether the user can view the industry.
     */
    public function canView(
        User $actor,
        Industry $target
    ): bool {
        if ($this->targetOutranksActor(
            $actor,
            $target
        )) {
            return false;
        }

        return $this->isAdmin($actor)
            && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can update the industry.
     */
    public function canUpdate(
        User $actor,
        Industry $target
    ): bool {
        if ($this->targetOutranksActor(
            $actor,
            $target
        )) {
            return false;
        }

        return $this->isAdmin($actor)
            && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can delete the industry.
     */
    public function canDelete(
        User $actor,
        Industry $target
    ): bool {
        if ($this->targetOutranksActor(
            $actor,
            $target
        )) {
            return false;
        }

        return $this->isAdmin($actor)
            && $this->activeChecker->canBeModified($target);
    }

    /**
     * Determine whether the user can restore the industry.
     */
    public function canRestore(
        User $actor,
        Industry $target
    ): bool {
        if ($this->targetOutranksActor(
            $actor,
            $target
        )) {
            return false;
        }

        return $this->isAdmin($actor)
            && $this->activeChecker->canBeRestoredOrForceDeleted($target);
    }

    /**
     * Determine whether the user can permanently delete the industry.
     */
    public function canForceDelete(
        User $actor,
        Industry $target
    ): bool {
        if ($this->targetOutranksActor(
            $actor,
            $target
        )) {
            return false;
        }

        return $this->activeChecker->canUserPerformAction(
            $actor,
            'restoreOrForceDelete',
            $target
        );
    }

    /**
     * Determine whether the user can assign the industry.
     */
    public function canAssign(
        User $actor,
        Industry $target
    ): bool {
        if ($this->targetOutranksActor(
            $actor,
            $target
        )) {
            return false;
        }

        return $actor->can('assign industries')
            && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the industry was created by a user who outranks the actor.
     *
     * Prevents admins from managing industries created by super admins.
     */
    private function targetOutranksActor(
        User $actor,
        Industry $target
    ): bool {
        if ($this->roleChecker->isSuperAdmin($actor)) {
            return false;
        }

        $creator = $target->creator;

        if (! $creator instanceof User) {
            return false;
        }

        return $this->roleChecker->isSuperAdmin(
            $creator
        );
    }
}
