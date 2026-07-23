<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Services\Comments\PolicyAuthorisationService;

class CommentPolicy
{
    /**
     * Inject the required services into the policy.
     */
    public function __construct(
        protected PolicyAuthorisationService $authorisationService
    ) {}

    /**
     * Determine whether the user can comment on the given post.
     */
    public function create(
        User $user,
        Post $post
    ): bool {
        return $this->authorisationService->canCreate(
            $user,
            $post
        );
    }

    /**
     * Determine whether the user can update the given comment.
     */
    public function update(
        User $user,
        Comment $comment
    ): bool {
        return $this->authorisationService->canUpdate(
            $user,
            $comment
        );
    }

    /**
     * Determine whether the user can delete the given comment.
     */
    public function delete(
        User $user,
        Comment $comment
    ): bool {
        return $this->authorisationService->canDelete(
            $user,
            $comment
        );
    }
}
