<?php

namespace App\Services\Industries;

use App\Models\Industry;
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
     * Check if industry is active (not soft-deleted).
     */
    public function isActive(
        Industry $industry
    ): bool {
        return ! $industry->trashed();
    }

    /**
     * Check if industry is soft-deleted.
     */
    public function isTrashed(
        Industry $industry
    ): bool {
        return $industry->trashed();
    }

    /**
     * Check if industry is active and can be updated or deleted.
     */
    public function canBeModified(
        Industry $industry
    ): bool {
        return $this->isActive(
            $industry
        );
    }

    /**
     * Check if industry is soft-deleted and can be restored or force-deleted.
     */
    public function canBeRestoredOrForceDeleted(
        Industry $industry
    ): bool {
        return $this->isTrashed(
            $industry
        );
    }

    /**
     * Check if the user can perform an action on the industry.
     */
    public function canUserPerformAction(
        User $actor,
        string $action,
        Industry $target
    ): bool {
        return match ($action) {
            'modify' => $this->roleChecker->isAdmin($actor) && $this->canBeModified($target),
            'restoreOrForceDelete' => $this->roleChecker->isAdmin($actor) && $this->canBeRestoredOrForceDeleted($target),
            default => false,
        };
    }
}
