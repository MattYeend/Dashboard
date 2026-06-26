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
        protected readonly ActiveCheckerService $activeChecker,
        protected readonly UserRoleCheckerService $roleChecker
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
    public function canView(User $actor, Contact $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $this->isAdmin($actor) && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function canUpdate(User $actor, Contact $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $this->isAdmin($actor) && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function canDelete(User $actor, Contact $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $this->isAdmin($actor) && $this->activeChecker->canBeModified($target);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function canRestore(User $actor, Contact $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $this->isAdmin($actor)
            && $this->activeChecker->canBeRestoredOrForceDeleted($target);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function canForceDelete(User $actor, Contact $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $this->activeChecker->canUserPerformAction(
            $actor,
            'restoreOrForceDelete',
            $target
        );
    }

    /**
     * Determine whether the target user outranks the acting user.
     *
     * A Super Admin cannot be managed by anyone other than another Super Admin.
     */
    private function targetOutranksActor(User $actor, Contact $target): bool
    {
        if ($this->roleChecker->isSuperAdmin($actor)) {
            return false;
        }

        $owner = $target->contactable;

        if (! $owner instanceof User) {
            return false;
        }

        return $this->roleChecker->isSuperAdmin($owner);
    }
}
