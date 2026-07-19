<?php

namespace App\Services\Comments;

use App\Actions\Like\LikeComment;
use App\Actions\Like\UnlikeComment;
use App\Http\Requests\Comments\StoreCommentRequest;
use App\Http\Requests\Comments\UpdateCommentRequest;
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
        protected readonly UpdaterService $updater,
        protected readonly DeleterService $destructor,
        protected readonly LikeComment $likeComment,
        protected readonly UnlikeComment $unlikeComment,
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
     * Update an existing comment.
     */
    public function update(
        UpdateCommentRequest $request,
        Comment $comment
    ): Comment {
        return $this->updater->update(
            $comment,
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

    /**
     * Like the given comment on behalf of the given user.
     */
    public function like(
        Comment $comment,
        User $actor
    ): void {
        $this->likeComment->handle($comment, $actor);
    }

    /**
     * Unlike the given comment on behalf of the given user.
     */
    public function unlike(
        Comment $comment,
        User $actor
    ): void {
        $this->unlikeComment->handle($comment, $actor);
    }
}
