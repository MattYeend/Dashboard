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
    public function isAdmin(User $user): bool
    {
        return $this->roleChecker->isAdmin($user);
    }

    /**
     * Determine whether the user can comment on the given post.
     *
     * Mirrors Post's own view gate, since commenting requires the
     * same access as viewing the post.
     */
    public function canCreate(User $actor, Post $post): bool
    {
        return $this->postAuthorisation->canView($actor, $post);
    }

    /**
     * Determine whether the user can delete the given comment.
     *
     * The comment's own author can always delete it, provided it is
     * not already trashed. Otherwise the same outrank + admin rules
     * as Post apply.
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
