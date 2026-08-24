<?php

namespace App\Services\Activities;

use App\Models\Activity;
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
     * Check if the activity is active (not soft-deleted).
     */
    public function isActive(Activity $activity): bool
    {
        return ! $activity->trashed();
    }

    /**
     * Check if activity is soft-deleted.
     */
    public function isTrashed(Activity $activity): bool
    {
        return $activity->trashed();
    }

    /**
     * Check if activity is active (not soft-deleted) and can be
     * updated/deleted.
     */
    public function canBeModified(Activity $activity): bool
    {
        return $this->isActive($activity);
    }

    /**
     * Check if activity is soft-deleted and can be restored/force-deleted.
     */
    public function canBeRestoredOrForceDeleted(Activity $activity): bool
    {
        return $this->isTrashed($activity);
    }

    /**
     * Check if user can modify activity (update/delete) or restore/force-delete
     * activity based on its active status.
     */
    public function canUserPerformAction(User $actor, string $action, Activity $target): bool
    {
        return match ($action) {
            'modify' => $this->roleChecker->isAdmin($actor) && $this->canBeModified($target),
            'restoreOrForceDelete' => $this->roleChecker->isAdmin($actor) && $this->canBeRestoredOrForceDeleted($target),
            default => false,
        };
    }
}
