<?php

namespace App\Services\OrderStatuses;

use App\Models\OrderStatus;
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
     * Check if orderStatus is active (not soft-deleted).
     */
    public function isActive(OrderStatus $orderStatus): bool
    {
        return ! $orderStatus->trashed();
    }

    /**
     * Check if orderStatus is soft-deleted.
     */
    public function isTrashed(OrderStatus $orderStatus): bool
    {
        return $orderStatus->trashed();
    }

    /**
     * Check if orderStatus is active (not soft-deleted) and can be
     * updated/deleted.
     */
    public function canBeModified(OrderStatus $orderStatus): bool
    {
        return $this->isActive($orderStatus);
    }

    /**
     * Check if orderStatus is soft-deleted and can be restored/force-deleted.
     */
    public function canBeRestoredOrForceDeleted(
        OrderStatus $orderStatus
    ): bool {
        return $this->isTrashed($orderStatus);
    }

    /**
     * Check if user can modify orderStatus (update/delete) or restore/force-delete
     * orderStatus based on its active status.
     */
    public function canUserPerformAction(
        User $actor,
        string $action,
        OrderStatus $target
    ): bool {
        return match ($action) {
            'modify' => $this->roleChecker->isAdmin($actor) && $this->canBeModified($target),
            'restoreOrForceDelete' => $this->roleChecker->isAdmin($actor) && $this->canBeRestoredOrForceDeleted($target),
            default => false,
        };
    }
}
