<?php

namespace App\Services\Comments;

use App\Http\Requests\Comments\StoreCommentRequest;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;

class ManagementService
{
    /**
     * Inject the required services into the management service.
     */
    public function __construct(
        protected readonly CreatorService $creator,
        protected readonly DeleterService $destructor,
    ) {}

    /**
     * Create a new comment on the given post.
     */
    public function store(
        StoreCommentRequest $request,
        Post $post
    ): Comment {
        return $this->creator->create(
            $post,
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Delete a comment.
     */
    public function destroy(
        Comment $comment,
        User $actor
    ): void {
        $this->destructor->delete($comment, $actor->id);
    }
}
