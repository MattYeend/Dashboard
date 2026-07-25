<?php

namespace App\Services\Contacts;

use App\Models\Contact;
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
     * Check if contact is active (not soft-deleted).
     */
    public function isActive(
        Contact $contact
    ): bool {
        return ! $contact->trashed();
    }

    /**
     * Check if contact is soft-deleted.
     */
    public function isTrashed(
        Contact $contact
    ): bool {
        return $contact->trashed();
    }

    /**
     * Check if contact is active (not soft-deleted) and can be
     * updated/deleted.
     */
    public function canBeModified(
        Contact $contact
    ): bool {
        return $this->isActive(
            $contact
        );
    }

    /**
     * Check if contact is soft-deleted and can be restored/force-deleted.
     */
    public function canBeRestoredOrForceDeleted(
        Contact $contact
    ): bool {
        return $this->isTrashed(
            $contact
        );
    }

    /**
     * Check if user can modify contact (update/delete) or restore/force-delete
     * contact based on its active status.
     */
    public function canUserPerformAction(
        User $actor,
        string $action,
        Contact $target,
    ): bool {
        return match ($action) {
            'modify' => $this->roleChecker->isAdmin($actor) && $this->canBeModified($target),
            'restoreOrForceDelete' => $this->roleChecker->isAdmin($actor) && $this->canBeRestoredOrForceDeleted($target),
            default => false,
        };
    }
}
