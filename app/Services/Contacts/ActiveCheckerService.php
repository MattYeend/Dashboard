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
    public function isActive(Contact $contact): bool
    {
        return ! $contact->trashed();
    }

    /**
     * Check if contact is soft-deleted.
     */
    public function isTrashed(Contact $contact): bool
    {
        return $contact->trashed();
    }

    /**
     * Check if contact is active (not soft-deleted) and can be
     * updated/deleted.
     */
    public function canBeModified(Contact $contact): bool
    {
        return $this->isActive($contact);
    }

    /**
     * Check if contact is soft-deleted and can be restored/force-deleted.
     */
    public function canBeRestoredOrForceDeleted(
        Contact $contact
    ): bool {
        return $this->isTrashed($contact);
    }

    /**
     * Check if user can modify contact (update/delete) or restore/force-delete
     * contact based on its active status.
     */
    public function canUserPerformAction(
        Contact $contact,
        string $action,
        User $user
    ): bool {
        if ($action === 'modify') {
            return $this->roleChecker->isAdmin($user) && $this->canBeModified(
                $contact
            );
        }

        if ($action === 'restoreOrForceDelete') {
            return $this->roleChecker->isAdmin($user) &&
                $this->canBeRestoredOrForceDeleted($contact);
        }

        throw new \InvalidArgumentException("Invalid action: {$action}");
    }
}
