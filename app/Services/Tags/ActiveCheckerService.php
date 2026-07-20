<?php

namespace App\Services\Tags;

use App\Models\Tag;
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
     * Check if tag is active (not soft-deleted).
     */
    public function isActive(Tag $tag): bool
    {
        return ! $tag->trashed();
    }

    /**
     * Check if tag is soft-deleted.
     */
    public function isTrashed(Tag $tag): bool
    {
        return $tag->trashed();
    }

    /**
     * Check if tag is active (not soft-deleted) and can be
     * updated/deleted.
     */
    public function canBeModified(Tag $tag): bool
    {
        return $this->isActive($tag);
    }

    /**
     * Check if tag is soft-deleted and can be restored/force-deleted.
     */
    public function canBeRestoredOrForceDeleted(Tag $tag): bool
    {
        return $this->isTrashed($tag);
    }

    /**
     * Check if user can modify tag (update/delete) or restore/force-delete
     * tag based on its active status.
     */
    public function canUserPerformAction(
        User $actor,
        string $action,
        Tag $target
    ): bool {
        return match ($action) {
            'modify' => $this->roleChecker->isAdmin($actor) && $this->canBeModified($target),
            'restoreOrForceDelete' => $this->roleChecker->isAdmin($actor) && $this->canBeRestoredOrForceDeleted($target),
            default => false,
        };
    }
}
