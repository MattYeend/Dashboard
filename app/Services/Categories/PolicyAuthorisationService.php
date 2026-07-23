<?php

namespace App\Services\Categories;

use App\Models\Category;
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
     * Check if category is active (not soft-deleted).
     */
    public function isActive(
        Category $category
    ): bool {
        return $this->activeChecker->isActive(
            $category
        );
    }

    /**
     * Check if category is soft-deleted.
     */
    public function isTrashed(
        Category $category
    ): bool {
        return $this->activeChecker->isTrashed(
            $category
        );
    }

    /**
     * Determine whether the user can view the category.
     */
    public function canView(
        User $actor,
        Category $target
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
     * Determine whether the user can update the category.
     */
    public function canUpdate(
        User $actor,
        Category $target
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
     * Determine whether the user can delete the category.
     */
    public function canDelete(
        User $actor,
        Category $target
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
     * Determine whether the user can restore the category.
     */
    public function canRestore(
        User $actor,
        Category $target
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
     * Determine whether the user can permanently delete the category.
     */
    public function canForceDelete(
        User $actor,
        Category $target
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
     * Determine whether the user can assign the category.
     */
    public function canAssign(
        User $actor,
        Category $target
    ): bool {
        if ($this->targetOutranksActor(
            $actor,
            $target
        )) {
            return false;
        }

        return $actor->can('assign category')
            && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the category was created by a user who outranks the actor.
     *
     * Prevents admins from managing categories created by super admins.
     */
    private function targetOutranksActor(
        User $actor,
        Category $target
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
