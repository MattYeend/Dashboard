<?php

namespace App\Services\Comments;

use App\Actions\Like\LikeComment;
use App\Actions\Like\UnlikeComment;
use App\Http\Requests\Comments\ImportCommentRequest;
use App\Http\Requests\Comments\StoreCommentRequest;
use App\Http\Requests\Comments\UpdateCommentRequest;
use App\Models\Comment;
use App\Models\User;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ManagementService
{
    /**
     * Inject the required services into the management service.
     */
    public function __construct(
        protected readonly CreatorService $creator,
        protected readonly UpdaterService $updater,
        protected readonly DeleterService $destructor,
        protected readonly RestorerService $restorer,
        protected readonly LikeComment $likeComment,
        protected readonly UnlikeComment $unlikeComment,
        protected readonly ImporterService $importer,
        protected readonly ExporterService $exporter,
    ) {}

    /**
     * Create a new comment.
     */
    public function store(StoreCommentRequest $request): Comment
    {
        return $this->creator->create(
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

    /**
     * Restore a soft-deleted comment.
     */
    public function restore(
        int $id,
        User $actor
    ): Comment {
        $comment = Comment::withTrashed()->findOrFail($id);

        return $this->restorer->restore($comment, $actor->id);
    }

    /**
     * Force delete a comment, permanently removing it from the database.
     */
    public function forceDelete(
        int $id,
        User $actor
    ): void {
        $comment = Comment::withTrashed()->findOrFail($id);
        $this->destructor->forceDelete($comment, $actor->id);
    }

    /**
     * Bulk restore comments.
     *
     * @return array{restored: array<int, int>, skipped: array<int, int>}
     */
    public function bulkRestore(
        array $ids,
        User $actor,
        callable $authoriseCallback
    ): array {
        $requestedIds = collect($ids)->unique()->values();

        $comments = Comment::onlyTrashed()
            ->whereIn('id', $requestedIds)
            ->get();

        $restored = [];

        foreach ($comments as $comment) {
            /** @var Comment $comment */
            $authoriseCallback($comment);
            $this->restorer->restore($comment, $actor->id);
            $restored[] = $comment->id;
        }

        return [
            'restored' => $restored,
            'skipped' => $requestedIds->diff($comments->pluck('id'))->values()->all(),
        ];
    }

    /**
     * Bulk soft delete comments.
     *
     * @return array{deleted: array<int, int>, skipped: array<int, int>}
     */
    public function bulkDelete(
        array $ids,
        User $actor,
        callable $authoriseCallback
    ): array {
        $requestedIds = collect($ids)->unique()->values();

        $comments = Comment::whereIn('id', $requestedIds)->get();

        $deleted = [];

        foreach ($comments as $comment) {
            /** @var Comment $comment */
            $authoriseCallback($comment);
            $this->destructor->delete($comment, $actor->id);
            $deleted[] = $comment->id;
        }

        return [
            'deleted' => $deleted,
            'skipped' => $requestedIds->diff($comments->pluck('id'))->values()->all(),
        ];
    }

    /**
     * Import comments from an uploaded file.
     *
     * @return array{imported: int, skipped: array<int, array{row: int, reason: string}>}
     */
    public function import(ImportCommentRequest $request): array
    {
        return $this->importer->import(
            $request->file('file'),
            $request->user()->id
        );
    }

    /**
     * Export comments matching the given filters as a CSV download.
     *
     * @param  array<string, mixed>  $filters
     */
    public function export(array $filters): StreamedResponse
    {
        return $this->exporter->export($filters);
    }
}
