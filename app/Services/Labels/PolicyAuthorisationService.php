<?php

namespace App\Services\Labels;

use App\Models\Label;
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
     * Check if label is active (not soft-deleted).
     */
    public function isActive(Label $label): bool
    {
        return $this->activeChecker->isActive($label);
    }

    /**
     * Check if label is soft-deleted.
     */
    public function isTrashed(Label $label): bool
    {
        return $this->activeChecker->isTrashed($label);
    }

    /**
     * Determine whether the user can view any labels.
     */
    public function canViewAny(User $actor): bool
    {
        return $actor->can('view any labels');
    }

    /**
     * Determine whether the user can create labels.
     */
    public function canCreate(User $actor): bool
    {
        return $actor->can('create labels');
    }

    /**
     * Determine whether the user can view the label.
     */
    public function canView(User $actor, Label $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('view labels')
            && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can update the label.
     */
    public function canUpdate(User $actor, Label $target): bool
    {
        if ($this->targetOutranksActor(
            $actor,
            $target
        )) {
            return false;
        }

        return $actor->can('edit labels')
            && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can delete the label.
     */
    public function canDelete(User $actor, Label $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('delete labels')
            && $this->activeChecker->canBeModified($target);
    }

    /**
     * Determine whether the user can restore the label.
     */
    public function canRestore(User $actor, Label $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('restore labels')
            && $this->activeChecker->canBeRestoredOrForceDeleted($target);
    }

    /**
     * Determine whether the user can permanently delete the label.
     */
    public function canForceDelete(User $actor, Label $target): bool
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
     * Determine whether the user can import labels.
     */
    public function canImport(User $actor): bool
    {
        return $actor->can('import labels');
    }

    /**
     * Determine whether the user can export labels.
     */
    public function canExport(User $actor): bool
    {
        return $actor->can('export labels');
    }

    /**
     * Determine whether the user can assign the label to a record.
     */
    public function canAssign(User $actor, Label $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('assign labels')
            && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the label was created by a user who outranks the actor.
     *
     * Prevents admins from managing labels created by super admins.
     */
    private function targetOutranksActor(User $actor, Label $target): bool
    {
        if ($this->roleChecker->isSuperAdmin(
            $actor
        )) {
            return false;
        }

        $creator = $target->creator;

        if (! $creator instanceof User) {
            return false;
        }

        return $this->roleChecker->isSuperAdmin($creator);
    }
}
