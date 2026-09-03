<?php

namespace App\Services\Organisations;

use App\Models\Organisation;
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
     * Check if organisation is active (not soft-deleted).
     */
    public function isActive(Organisation $organisation): bool
    {
        return ! $organisation->trashed();
    }

    /**
     * Check if organisation is soft-deleted.
     */
    public function isTrashed(Organisation $organisation): bool
    {
        return $organisation->trashed();
    }

    /**
     * Check if organisation is active and can be updated or deleted.
     */
    public function canBeModified(Organisation $organisation): bool
    {
        return $this->isActive($organisation);
    }

    /**
     * Check if organisation is soft-deleted and can be restored or force-deleted.
     */
    public function canBeRestoredOrForceDeleted(Organisation $organisation): bool
    {
        return $this->isTrashed($organisation);
    }

    /**
     * Check if the user can perform an action on the organisation.
     */
    public function canUserPerformAction(User $actor, string $action, Organisation $target): bool
    {
        return match ($action) {
            'modify' => $this->roleChecker->isAdmin($actor) && $this->canBeModified($target),
            'restoreOrForceDelete' => $this->roleChecker->isAdmin($actor) && $this->canBeRestoredOrForceDeleted($target),
            default => false,
        };
    }
}
