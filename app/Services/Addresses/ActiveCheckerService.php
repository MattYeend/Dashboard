<?php

namespace App\Services\Addresses;

use App\Models\Address;
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
     * Check if address is active (not soft-deleted).
     */
    public function isActive(
        Address $address
    ): bool {
        return ! $address->trashed();
    }

    /**
     * Check if address is soft-deleted.
     */
    public function isTrashed(
        Address $address
    ): bool {
        return $address->trashed();
    }

    /**
     * Check if address is active (not soft-deleted) and can be
     * updated/deleted.
     */
    public function canBeModified(
        Address $address
    ): bool {
        return $this->isActive(
            $address
        );
    }

    /**
     * Check if address is soft-deleted and can be restored/force-deleted.
     */
    public function canBeRestoredOrForceDeleted(
        Address $address
    ): bool {
        return $this->isTrashed(
            $address
        );
    }

    /**
     * Check if user can modify address (update/delete) or restore/force-delete
     * address based on its active status.
     */
    public function canUserPerformAction(
        User $actor,
        string $action,
        Address $target,
    ): bool {
        return match ($action) {
            'modify' => $this->roleChecker->isAdmin($actor) && $this->canBeModified($target),
            'restoreOrForceDelete' => $this->roleChecker->isAdmin($actor) && $this->canBeRestoredOrForceDeleted($target),
            default => false,
        };
    }
}
