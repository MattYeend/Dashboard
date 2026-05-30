<?php

namespace App\Services\Contacts;

use App\Models\Contact;
use App\Models\User;
use App\Services\UserRoleCheckerService;

class PolicyAuthorisationService
{
    /**
     * Inject the required services into the policy authorisation service.
     */
    public function __construct(
        protected ActiveCheckerService $activeChecker,
        protected UserRoleCheckerService $roleChecker
    ) {}

    /**
     * Check if user is a regular user, admin, or super admin.
     */
    public function isUser(User $user): bool
    {
        return $this->roleChecker->isUser($user);
    }

    /**
     * Check if user is admin or super admin.
     */
    public function isAdmin(User $user): bool
    {
        return $this->roleChecker->isAdmin($user);
    }

    /**
     * Check if contact is active (not soft-deleted).
     */
    public function isActive(Contact $contact): bool
    {
        return $this->activeChecker->isActive($contact);
    }

    /**
     * Check if contact is soft-deleted.
     */
    public function isTrashed(Contact $contact): bool
    {
        return $this->activeChecker->isTrashed($contact);
    }

    /**
     * Determine whether the user can view the model.
     * Only admins can view company contacts.
     */
    public function canView(User $user, Contact $contact): bool
    {
        return $this->isAdmin($user) && $this->activeChecker->isActive(
            $contact
        );
    }

    /**
     * Determine whether the user can update the model.
     */
    public function canUpdate(User $user, Contact $contact): bool
    {
        return $this->isAdmin($user) && $this->activeChecker->isActive(
            $contact
        );
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function canDelete(User $user, Contact $contact): bool
    {
        return $this->isAdmin($user) && $this->activeChecker->canBeModified(
            $contact
        );
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function canRestore(User $user, Contact $contact): bool
    {
        return $this->isAdmin($user) &&
            $this->activeChecker->canBeRestoredOrForceDeleted($contact);
    }

    /**
     * Determine whether the user can permanently delete the model.
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
