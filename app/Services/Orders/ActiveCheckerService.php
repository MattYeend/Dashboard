<?php

namespace App\Services\Orders;

use App\Models\Order;
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
     * Check if order is active (not soft-deleted).
     */
    public function isActive(Order $order): bool
    {
        return ! $order->trashed();
    }

    /**
     * Check if order is soft-deleted.
     */
    public function isTrashed(Order $order): bool
    {
        return $order->trashed();
    }

    /**
     * Check if order is active (not soft-deleted) and can be
     * updated/deleted.
     */
    public function canBeModified(Order $order): bool
    {
        return $this->isActive($order);
    }

    /**
     * Check if order is soft-deleted and can be restored/force-deleted.
     */
    public function canBeRestoredOrForceDeleted(
        Order $order
    ): bool {
        return $this->isTrashed($order);
    }

    /**
     * Check if user can modify order (update/delete) or restore/force-delete
     * order based on its active status.
     */
    public function canUserPerformAction(
        User $actor,
        string $action,
        Order $target,
    ): bool {
        return match ($action) {
            'modify' => $this->roleChecker->isAdmin($actor) && $this->canBeModified($target),
            'restoreOrForceDelete' => $this->roleChecker->isAdmin($actor) && $this->canBeRestoredOrForceDeleted($target),
            default => false,
        };
    }
}
