<?php

declare(strict_types=1);

namespace App\Services\PaymentStatuses;

use App\Models\PaymentStatus;
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
     * Check if payment status is active (not soft-deleted).
     */
    public function isActive(PaymentStatus $paymentStatus): bool
    {
        return ! $paymentStatus->trashed();
    }

    /**
     * Check if payment status is soft-deleted.
     */
    public function isTrashed(PaymentStatus $paymentStatus): bool
    {
        return $paymentStatus->trashed();
    }

    /**
     * Check if payment status is active (not soft-deleted) and can be
     * updated.
     */
    public function canBeModified(PaymentStatus $paymentStatus): bool
    {
        return $this->isActive($paymentStatus);
    }

    /**
     * Check if the status is a protected system status.
     */
    public function isSystem(PaymentStatus $paymentStatus): bool
    {
        return $paymentStatus->isSystem();
    }

    /**
     * Check if the status can be deleted: active and not a system status.
     */
    public function canBeDeleted(PaymentStatus $paymentStatus): bool
    {
        return $this->canBeModified($paymentStatus) && ! $this->isSystem($paymentStatus);
    }

    /**
     * Check if payment status is soft-deleted and can be restored or
     * force-deleted.
     */
    public function canBeRestoredOrForceDeleted(
        PaymentStatus $paymentStatus
    ): bool {
        return $this->isTrashed($paymentStatus);
    }

    /**
     * Check if user can modify, delete, restore or force-delete the payment
     * status based on its active and system state.
     */
    public function canUserPerformAction(
        User $actor,
        string $action,
        PaymentStatus $target,
    ): bool {
        return match ($action) {
            'modify' => $this->roleChecker->isAdmin($actor) && $this->canBeModified($target),
            'delete' => $this->roleChecker->isAdmin($actor) && $this->canBeDeleted($target),
            'restoreOrForceDelete' => $this->roleChecker->isAdmin($actor) && $this->canBeRestoredOrForceDeleted($target),
            default => false,
        };
    }
}
