<?php

namespace App\Services\NotificationBroadcasts;

use App\Models\NotificationBroadcast;
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
     * Check if the notification broadcast is active (not soft-deleted).
     */
    public function isActive(NotificationBroadcast $notificationBroadcast): bool
    {
        return ! $notificationBroadcast->trashed();
    }

    /**
     * Check if the notification broadcast is soft-deleted.
     */
    public function isTrashed(NotificationBroadcast $notificationBroadcast): bool
    {
        return $notificationBroadcast->trashed();
    }

    /**
     * Check if the notification broadcast is active and can be updated or deleted.
     */
    public function canBeModified(NotificationBroadcast $notificationBroadcast): bool
    {
        return $this->isActive($notificationBroadcast);
    }

    /**
     * Check if the notification broadcast is soft-deleted and can be restored or force-deleted.
     */
    public function canBeRestoredOrForceDeleted(NotificationBroadcast $notificationBroadcast): bool
    {
        return $this->isTrashed($notificationBroadcast);
    }

    /**
     * Check if the user can perform an action on the notification broadcast.
     */
    public function canUserPerformAction(
        User $actor,
        string $action,
        NotificationBroadcast $target
    ): bool {
        return match ($action) {
            'modify' => $this->roleChecker->isAdmin($actor) && $this->canBeModified($target),
            'restoreOrForceDelete' => $this->roleChecker->isAdmin($actor) && $this->canBeRestoredOrForceDeleted($target),
            default => false,
        };
    }
}
