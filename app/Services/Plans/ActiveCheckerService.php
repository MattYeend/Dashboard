<?php

namespace App\Services\Plans;

use App\Models\Plan;
use App\Models\User;
use App\Services\UserRoleCheckerService;

class ActiveCheckerService
{
    /**
     * Inject the required services into the active checker service.
     */
    public function __construct(
        protected readonly UserRoleCheckerService $roleChecker
    ) {}

    /**
     * Check if plan is active (not soft-deleted).
     */
    public function isActive(Plan $plan): bool
    {
        return ! $plan->trashed();
    }

    /**
     * Check if plan is soft-deleted.
     */
    public function isTrashed(Plan $plan): bool
    {
        return $plan->trashed();
    }

    /**
     * Check if plan is active and can be updated or deleted.
     */
    public function canBeModified(Plan $plan): bool
    {
        return $this->isActive($plan);
    }

    /**
     * Check if plan is soft-deleted and can be restored or force-deleted.
     */
    public function canBeRestoredOrForceDeleted(Plan $plan): bool
    {
        return $this->isTrashed($plan);
    }

    /**
     * Check if the user can perform an action on the plan.
     */
    public function canUserPerformAction(
        User $actor,
        string $action,
        Plan $target
    ): bool {
        return match ($action) {
            'modify' => $this->roleChecker->isAdmin($actor) && $this->canBeModified($target),
            'restoreOrForceDelete' => $this->roleChecker->isAdmin($actor) && $this->canBeRestoredOrForceDeleted($target),
            default => false,
        };
    }
}
