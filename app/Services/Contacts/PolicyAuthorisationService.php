<?php

namespace App\Services\Contacts;

use App\Models\Contact;
use App\Models\User;
use App\Services\UserRoleCheckerService;

class PolicyAuthorisationService
{
    /**
     * Inject the required services into the policy authorisation service.
     *
     * @param ActiveCheckerService $activeChecker
     * @param UserRoleCheckerService $roleChecker
     */
    public function __construct(
        protected ActiveCheckerService $activeChecker,
        protected UserRoleCheckerService $roleChecker
    ) {
    }

    /**
     * Check if user is a regular user, admin, or super admin.
     *
     * @param  User $user
     *
     * @return bool
     */
    public function isUser(User $user): bool
    {
        return $this->roleChecker->isUser($user);
    }

    /**
     * Check if user is admin or super admin.
     *
     * @param  User $user
     *
     * @return bool
     */
    public function isAdmin(User $user): bool
    {
        return $this->roleChecker->isAdmin($user);
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
        return $this->activeChecker->isActive($contact);
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
        return $this->activeChecker->isTrashed($contact);
    }

    /**
     * Determine whether the user can view the model.
     * Only admins can view company contacts.
     *
     * @param  User $user
     * @param  Contact $contact
     *
     * @return bool
     */
    public function canView(User $user, Contact $contact): bool
    {
        return $this->isAdmin($user) && $this->activeChecker->isActive(
            $contact
        );
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  User $user
     * @param  Contact $contact
     *
     * @return bool
     */
    public function canUpdate(User $user, Contact $contact): bool
    {
        return $this->isAdmin($user) && $this->activeChecker->isActive(
            $contact
        );
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  User $user
     * @param  Contact $contact
     *
     * @return bool
     */
    public function canDelete(User $user, Contact $contact): bool
    {
        return $this->isAdmin($user) && $this->activeChecker->canBeModified(
            $contact
        );
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  User $user
     * @param  Contact $contact
     *
     * @return bool
     */
    public function canRestore(User $user, Contact $contact): bool
    {
        return $this->isAdmin($user) &&
            $this->activeChecker->canBeRestoredOrForceDeleted($contact);
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  User $user
     * @param  Contact $contact
     *
     * @return bool
     */
    public function canForceDelete(User $user, Contact $contact): bool
    {
        return $this->activeChecker->canUserPerformAction(
            $contact,
            'restoreOrForceDelete',
            $user
        );
    }
}
