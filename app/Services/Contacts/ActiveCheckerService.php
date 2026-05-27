<?php

namespace App\Services\Contacts;

use App\Models\Contact;
use App\Models\User;
use App\Services\UserRoleCheckerService;

class ActiveCheckerService
{
    /**
     * Inject the required services into the active checker service.
     *
     * @param UserRoleCheckerService $roleChecker
     */
    public function __construct(
        protected UserRoleCheckerService $roleChecker
    ) {
    }

    /**
     * Check if contact is active (not soft-deleted).
     *
     * @param  Contact $contact
     *
     * @return bool
     */
    public function isActive(Contact $contact): bool
    {
        return ! $contact->trashed();
    }

    /**
     * Check if contact is soft-deleted.
     *
     * @param  Contact $contact
     *
     * @return bool
     */
    public function isTrashed(Contact $contact): bool
    {
        return $contact->trashed();
    }

    /**
     * Check if contact is active (not soft-deleted) and can be
     * updated/deleted.
     *
     * @param  Contact $contact
     *
     * @return bool
     */
    public function canBeModified(Contact $contact): bool
    {
        return $this->isActive($contact);
    }

    /**
     * Check if contact is soft-deleted and can be restored/force-deleted.
     *
     * @param  Contact $contact
     *
     * @return bool
     */
    public function canBeRestoredOrForceDeleted(
        Contact $contact
    ): bool {
        return $this->isTrashed($contact);
    }

    /**
     * Check if user can modify contact (update/delete) or restore/force-delete
     * contact based on its active status.
     *
     * @param  Contact $contact
     * @param  string $action The action being checked, either 'modify' or
     * 'restoreOrForceDelete'.
     * @param  User $user The user performing the action, used for admin check
     * in the callback.
     *
     * @return bool
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
