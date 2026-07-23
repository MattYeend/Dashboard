<?php

namespace App\Services\Comments;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Services\Posts\PolicyAuthorisationService as PostPolicyAuthorisationService;
use App\Services\UserRoleCheckerService;

class PolicyAuthorisationService
{
    /**
     * Inject the required services into the policy authorisation service.
     */
    public function __construct(
        protected readonly ActiveCheckerService $activeChecker,
        protected readonly UserRoleCheckerService $roleChecker,
        protected readonly PostPolicyAuthorisationService $postAuthorisation
    ) {}

    /**
     * Check if user is admin or super admin.
     */
    public function isAdmin(
        User $user
    ): bool {
        return $this->roleChecker->isAdmin(
            $user
        );
    }

    /**
     * Check if user is a regular user, admin, or super admin.
     */
    public function isUser(
        User $user
    ): bool {
        return $this->roleChecker->isUser(
            $user
        );
    }

    /**
     * Check if comment is active (not soft-deleted).
     */
    public function isActive(
        Comment $comment
    ): bool {
        return $this->activeChecker->isActive(
            $comment
        );
    }

    /**
     * Check if comment is soft-deleted.
     */
    public function isTrashed(
        Comment $comment
    ): bool {
        return $this->activeChecker->isTrashed(
            $comment
        );
    }

    /**
     * Determine whether the user can view any comments.
     */
    public function canViewAny(
        User $actor
    ): bool {
        return $this->isAdmin(
            $actor
        );
    }

    /**
     * Determine whether the user can view the given comment.
     */
    public function canView(
        User $actor,
        Comment $comment
    ): bool {
        if ($this->targetOutranksActor($actor, $comment)) {
            return false;
        }

        return $this->isAdmin($actor) && $this->activeChecker->isActive($comment);
    }

    /**
     * Determine whether the user can restore the given comment.
     */
    public function canRestore(
        User $actor,
        Comment $comment
    ): bool {
        if ($this->targetOutranksActor($actor, $comment)) {
            return false;
        }

        return $this->isAdmin($actor) && $this->activeChecker->canBeModified($comment);
    }

    /**
     * Determine whether the user can permanently delete the given comment.
     */
    public function canForceDelete(
        User $actor,
        Comment $comment
    ): bool {
        if ($this->targetOutranksActor($actor, $comment)) {
            return false;
        }

        return $this->activeChecker->canUserPerformAction(
            $actor,
            'restoreOrForceDelete',
            $comment
        );
    }

    /**
     * Determine whether the user can comment on the given post.
     *
     * Mirrors Post's own view gate, since commenting requires the
     * same access as viewing the post.
     */
    public function canCreate(
        User $actor,
        Post $post
    ): bool {
        return $this->postAuthorisation->canView(
            $actor,
            $post
        );
    }

    /**
     * Determine whether the user can update the given comment.
     *
     * Only the comment's own author can edit it, provided it is not
     * already trashed. Unlike delete, admins cannot edit someone
     * else's comment content, only remove it.
     */
    public function canUpdate(
        User $actor,
        Comment $comment
    ): bool {
        return $actor->id === $comment->created_by
            && $this->activeChecker->canBeModified($comment);
    }

    /**
     * Determine whether the user can delete the given comment.
     *
     * The comment's own author can always delete it, provided it is
     * not already trashed. Otherwise the same outrank + admin rules
     * as Post apply.
     */
    public function canDelete(
        User $actor,
        Comment $comment
    ): bool {
        if ($actor->id === $comment->created_by) {
            return $this->activeChecker->canBeModified($comment);
        }

        if ($this->targetOutranksActor($actor, $comment)) {
            return false;
        }

        return $this->activeChecker->canUserPerformAction(
            $actor,
            'modify',
            $comment
        );
    }

    /**
     * Determine whether the comment was created by a user who outranks the actor.
     *
     * Prevents admins from deleting comments created by super admins.
     */
    private function targetOutranksActor(
        User $actor,
        Comment $target
    ): bool {
        if ($this->roleChecker->isSuperAdmin($actor)) {
            return false;
        }

        $creator = $target->creator;

        if (! $creator instanceof User) {
            return false;
        }

        return $this->roleChecker->isSuperAdmin(
            $creator
        );
    }
}
