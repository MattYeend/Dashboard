<?php

namespace App\Services\Comments;

use App\Actions\DeleteResource;
use App\Models\Comment;
use App\Models\Log;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\DB;

class DeleterService
{
    /**
     * Inject the required services into the deleter service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
        protected readonly DeleteResource $deleteResource,
    ) {}

    /**
     * Soft delete a comment.
     *
     * @throws \Exception
     */
    public function delete(
        Comment $comment,
        int $deletedBy,
        ?User $actor = null
    ): bool {
        $actor ??= User::findOrFail($deletedBy);

        return $this->deleteResource->handle(
            $comment,
            function (Comment $comment) use ($actor, $deletedBy): void {
                $comment->deleted_by = $deletedBy;
                $comment->deleted_at = now();
                $comment->save();

                $this->auditLogService->record(
                    Log::ACTION_DELETE_COMMENT,
                    $actor,
                    $comment,
                    ['before' => $this->auditLogService->snapshot($comment)],
                );
            });
    }

    /**
     * Force delete a comment (permanent deletion).
     *
     * @throws \Exception
     */
    public function forceDelete(Comment $comment, int $deletedBy): bool
    {
        $actor = User::findOrFail($deletedBy);

        return $this->deleteResource->forceHandle(
            $comment,
            function (Comment $comment) use ($actor): void {
                $this->auditLogService->record(
                    Log::ACTION_FORCE_DELETE_COMMENT,
                    $actor,
                    $comment,
                    ['before' => $this->auditLogService->snapshot($comment)],
                );
            });
    }

    /**
     * Delete multiple comments.
     *
     * @throws \Exception
     */
    public function deleteMultiple(array $commentIds, int $deletedBy): int
    {
        $count = 0;

        DB::transaction(function () use ($commentIds, $deletedBy, &$count) {
            $actor = User::findOrFail($deletedBy);
            $comments = Comment::whereIn('id', $commentIds)->get();

            foreach ($comments as $comment) {
                if ($this->delete($comment, $deletedBy, $actor)) {
                    $count++;
                }
            }
        });

        return $count;
    }
}
