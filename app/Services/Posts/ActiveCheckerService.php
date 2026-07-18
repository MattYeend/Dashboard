<?php

namespace App\Services\Posts;

use App\Models\Post;
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
     * Check if post is active (not soft-deleted).
     */
    public function isActive(Post $post): bool
    {
        return ! $post->trashed();
    }

    /**
     * Check if post is soft-deleted.
     */
    public function isTrashed(Post $post): bool
    {
        return $post->trashed();
    }

    /**
     * Check if post is active (not soft-deleted) and can be
     * updated/deleted.
     */
    public function canBeModified(Post $post): bool
    {
        return $this->isActive($post);
    }

    /**
     * Check if post is soft-deleted and can be restored/force-deleted.
     */
    public function canBeRestoredOrForceDeleted(
        Post $post
    ): bool {
        return $this->isTrashed($post);
    }

    /**
     * Check if user can modify post (update/delete) or restore/force-delete
     * post based on its active status.
     */
    public function canUserPerformAction(
        User $actor,
        string $action,
        Post $target
    ): bool {
        return match ($action) {
            'modify' => $this->roleChecker->isAdmin($actor) && $this->canBeModified($target),
            'restoreOrForceDelete' => $this->roleChecker->isAdmin($actor) && $this->canBeRestoredOrForceDeleted($target),
            default => false,
        };
    }
}
