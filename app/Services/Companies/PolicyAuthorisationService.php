<?php

namespace App\Services\Companies;

use App\Models\Company;
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
     * Check if company is active (not soft-deleted).
     */
    public function isActive(
        Company $company
    ): bool {
        return $this->activeChecker->isActive(
            $company
        );
    }

    /**
     * Check if company is soft-deleted.
     */
    public function isTrashed(
        Company $company
    ): bool {
        return $this->activeChecker->isTrashed(
            $company
        );
    }

    /**
     * Determine whether the user can view the company.
     */
    public function canView(
        User $actor,
        Company $target
    ): bool {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $this->isAdmin($actor) && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can update the company.
     */
    public function canUpdate(
        User $actor,
        Company $target
    ): bool {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $this->isAdmin($actor) && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can delete the company.
     */
    public function canDelete(
        User $actor,
        Company $target
    ): bool {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $this->isAdmin($actor) && $this->activeChecker->canBeModified($target);
    }

    /**
     * Determine whether the user can restore the company.
     */
    public function canRestore(
        User $actor,
        Company $target
    ): bool {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $this->isAdmin($actor) && $this->activeChecker->canBeRestoredOrForceDeleted($target);
    }

    /**
     * Determine whether the user can permanently delete the company.
     */
    public function canForceDelete(
        User $actor,
        Company $target
    ): bool {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $this->activeChecker->canUserPerformAction(
            $actor,
            'restoreOrForceDelete',
            $target
        );
    }

    /**
     * Determine whether the user can change the company's industry.
     */
    public function canChangeIndustry(
        User $actor,
        Company $target
    ): bool {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('change company industry') && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the company was created by a user who outranks the actor.
     *
     * Prevents admins from managing companies created by super admins.
     */
    private function targetOutranksActor(
        User $actor,
        Company $target
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
