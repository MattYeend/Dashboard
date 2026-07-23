<?php

namespace App\Services\Comments;

use App\Models\Comment;
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
     * Check if comment is active (not soft-deleted).
     */
    public function isActive(
        Comment $comment
    ): bool {
        return ! $comment->trashed();
    }

    /**
     * Check if comment is soft-deleted.
     */
    public function isTrashed(
        Comment $comment
    ): bool {
        return $comment->trashed();
    }

    /**
     * Check if comment is active (not soft-deleted) and can be modified.
     */
    public function canBeModified(
        Comment $comment
    ): bool {
        return $this->isActive(
            $comment
        );
    }

    /**
     * Check if user can modify (delete) the comment based on its active status.
     */
    public function canUserPerformAction(
        User $actor,
        string $action,
        Comment $target
    ): bool {
        return match ($action) {
            'modify' => $this->roleChecker->isAdmin($actor) && $this->canBeModified($target),
            default => false,
        };
    }
}
