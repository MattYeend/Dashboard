<?php

namespace App\Services\Comments;

use App\Models\Comment;
use App\Models\User;
use App\Services\UserRoleCheckerService;

class PolicyAuthorisationService
{
    /**
     * Inject the required services into the policy authorisation service.
     */
    public function __construct(
        protected readonly ActiveCheckerService $activeChecker,
        protected readonly UserRoleCheckerService $roleChecker,
    ) {}

    /**
     * Check if user is admin or super admin.
     */
    public function isAdmin(User $user): bool
    {
        return $this->roleChecker->isAdmin($user);
    }

    /**
     * Check if user is a regular user, admin, or super admin.
     */
    public function isUser(User $user): bool
    {
        return $this->roleChecker->isUser($user);
    }

    /**
     * Check if comment is active (not soft-deleted).
     */
    public function isActive(Comment $comment): bool
    {
        return $this->activeChecker->isActive($comment);
    }

    /**
     * Check if comment is soft-deleted.
     */
    public function isTrashed(Comment $comment): bool
    {
        return $this->activeChecker->isTrashed($comment);
    }

    /**
     * Determine whether the user can view any comments.
     */
    public function canViewAny(User $actor): bool
    {
        return $this->isAdmin($actor);
    }

    /**
     * Determine whether the user can view the given comment.
     */
    public function canView(User $actor, Comment $comment): bool
    {
        if ($this->targetOutranksActor($actor, $comment)) {
            return false;
        }

        return $this->isAdmin($actor)
            && $this->activeChecker->isActive($comment);
    }

    /**
     * Determine whether the user can restore the given comment.
     */
    public function canRestore(User $actor, Comment $comment): bool
    {
        if ($this->targetOutranksActor($actor, $comment)) {
            return false;
        }

        return $this->isAdmin($actor)
            && $this->activeChecker->canBeModified($comment);
    }

    /**
     * Determine whether the user can permanently delete the given comment.
     */
    public function canForceDelete(User $actor, Comment $comment): bool
    {
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
     * Determine whether the user can create a comment.
     */
    public function canCreate(User $actor): bool
    {
        return $actor->can('create comments');
    }

    /**
     * Determine whether the user can update the given comment.
     */
    public function canUpdate(User $actor, Comment $comment): bool
    {
        return $actor->id === $comment->created_by
            && $this->activeChecker->canBeModified($comment);
    }

    /**
     * Determine whether the user can delete the given comment.
     */
    public function canDelete(User $actor, Comment $comment): bool
    {
        if ($actor->id === $comment->created_by) {
            return $this->activeChecker->canBeModified($comment);
        }

        if ($this->targetOutranksActor($actor, $comment)) {
            return false;
        }

        return $this->activeChecker->canUserPerformAction($actor, 'modify', $comment);
    }

    /**
     * Determine whether the user can import comments.
     */
    public function canImport(User $actor): bool
    {
        return $actor->can('import comments');
    }

    /**
     * Determine whether the user can export comments.
     */
    public function canExport(User $actor): bool
    {
        return $actor->can('export comments');
    }

    /**
     * Determine whether the comment was created by a user who outranks the actor.
     *
     * Prevents admins from deleting comments created by super admins.
     */
    private function targetOutranksActor(User $actor, Comment $target): bool
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
