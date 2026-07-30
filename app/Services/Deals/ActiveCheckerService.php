<?php

namespace App\Services\Deals;

use App\Models\Deal;
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
     * Check if deal is active (not soft-deleted).
     */
    public function isActive(Deal $deal): bool
    {
        return ! $deal->trashed();
    }

    /**
     * Check if deal is soft-deleted.
     */
    public function isTrashed(Deal $deal): bool
    {
        return $deal->trashed();
    }

    /**
     * Check if deal is active (not soft-deleted) and can be
     * updated/deleted.
     */
    public function canBeModified(Deal $deal): bool
    {
        return $this->isActive($deal);
    }

    /**
     * Check if deal is soft-deleted and can be restored/force-deleted.
     */
    public function canBeRestoredOrForceDeleted(
        Deal $deal
    ): bool {
        return $this->isTrashed($deal);
    }

    /**
     * Check if user can modify deal (update/delete) or restore/force-delete
     * deal based on its active status.
     */
    public function canUserPerformAction(
        User $actor,
        string $action,
        Deal $target
    ): bool {
        return match ($action) {
            'modify' => $this->roleChecker->isAdmin($actor) && $this->canBeModified($target),
            'restoreOrForceDelete' => $this->roleChecker->isAdmin($actor) && $this->canBeRestoredOrForceDeleted($target),
            default => false,
        };
    }
}
