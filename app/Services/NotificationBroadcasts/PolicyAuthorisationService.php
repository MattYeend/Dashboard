<?php

namespace App\Services\NotificationBroadcasts;

use App\Models\NotificationBroadcast;
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
     * Determine whether the user can view any notification broadcasts.
     */
    public function canViewAny(User $actor): bool
    {
        return $actor->can('view notifications');
    }

    /**
     * Determine whether the user can create notification broadcasts.
     */
    public function canCreate(User $actor): bool
    {
        return $actor->can('create notifications');
    }

    /**
     * Determine whether the user can view the notification broadcast.
     */
    public function canView(User $actor, NotificationBroadcast $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('view notifications')
            && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can update the notification broadcast.
     *
     * A broadcast that has already been sent is a record, not a draft — it
     * can no longer be edited, regardless of permission.
     */
    public function canUpdate(User $actor, NotificationBroadcast $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        if ($target->sent_at !== null) {
            return false;
        }

        return $actor->can('edit notifications')
            && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can delete the notification broadcast.
     */
    public function canDelete(User $actor, NotificationBroadcast $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('delete notifications')
            && $this->activeChecker->canBeModified($target);
    }

    /**
     * Determine whether the user can restore the notification broadcast.
     */
    public function canRestore(User $actor, NotificationBroadcast $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $this->isAdmin($actor)
            && $this->activeChecker->canBeRestoredOrForceDeleted($target);
    }

    /**
     * Determine whether the user can permanently delete the notification broadcast.
     */
    public function canForceDelete(User $actor, NotificationBroadcast $target): bool
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
     * Determine whether the user can send the notification broadcast.
     *
     * A broadcast can only be sent once.
     */
    public function canSend(User $actor, NotificationBroadcast $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        if ($target->sent_at !== null) {
            return false;
        }

        return $actor->can('send notifications')
            && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the notification broadcast was created by a user
     * who outranks the actor.
     *
     * Prevents admins from managing broadcasts created by super admins.
     */
    private function targetOutranksActor(User $actor, NotificationBroadcast $target): bool
    {
        if ($this->roleChecker->isSuperAdmin($actor)) {
            return false;
        }

        $creator = $target->creator;

        if (! $creator instanceof User) {
            return false;
        }

        return $this->roleChecker->isSuperAdmin($creator);
    }
}
