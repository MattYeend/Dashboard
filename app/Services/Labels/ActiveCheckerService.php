<?php

namespace App\Services\Labels;

use App\Models\Label;
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
     * Check if label is active (not soft-deleted).
     */
    public function isActive(Label $label): bool
    {
        return ! $label->trashed();
    }

    /**
     * Check if label is soft-deleted.
     */
    public function isTrashed(Label $label): bool
    {
        return $label->trashed();
    }

    /**
     * Check if label is active and can be updated or deleted.
     */
    public function canBeModified(Label $label): bool
    {
        return $this->isActive($label);
    }

    /**
     * Check if label is soft-deleted and can be restored or force-deleted.
     */
    public function canBeRestoredOrForceDeleted(Label $label): bool
    {
        return $this->isTrashed($label);
    }

    /**
     * Check if the user can perform an action on the label.
     */
    public function canUserPerformAction(
        User $actor,
        string $action,
        Label $target
    ): bool {
        return match ($action) {
            'modify' => $this->roleChecker->isAdmin($actor) && $this->canBeModified($target),
            'restoreOrForceDelete' => $this->roleChecker->isAdmin($actor) && $this->canBeRestoredOrForceDeleted($target),
            default => false,
        };
    }
}
