<?php

namespace App\Services\Attachments;

use App\Models\Attachment;
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
     * Check if attachment is active (not soft-deleted).
     */
    public function isActive(Attachment $attachment): bool
    {
        return ! $attachment->trashed();
    }

    /**
     * Check if attachment is soft-deleted.
     */
    public function isTrashed(Attachment $attachment): bool
    {
        return $attachment->trashed();
    }

    /**
     * Check if attachment is active (not soft-deleted) and can be
     * updated/deleted.
     */
    public function canBeModified(Attachment $attachment): bool
    {
        return $this->isActive($attachment);
    }

    /**
     * Check if attachment is soft-deleted and can be restored/force-deleted.
     */
    public function canBeRestoredOrForceDeleted(Attachment $attachment): bool
    {
        return $this->isTrashed($attachment);
    }

    /**
     * Check if user can modify attachment (update/delete) or restore/
     * force-delete attachment based on its active status.
     */
    public function canUserPerformAction(User $actor, string $action, Attachment $target): bool
    {
        return match ($action) {
            'modify' => $this->roleChecker->isAdmin($actor) && $this->canBeModified($target),
            'restoreOrForceDelete' => $this->roleChecker->isAdmin($actor) && $this->canBeRestoredOrForceDeleted($target),
            default => false,
        };
    }
}
