<?php

namespace App\Services\Comments;

use App\Actions\RestoreResource;
use App\Models\Comment;
use App\Models\Log;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class RestorerService
{
    /**
     * Inject the required services into the restorer service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
        protected readonly RestoreResource $restoreResource,
    ) {}

    /**
     * Restore a soft-deleted comment.
     *
     * @throws \Exception
     */
    public function restore(
        Comment $comment,
        int $restoredBy,
        ?User $actor = null
    ): Comment {
        $actor ??= User::findOrFail($restoredBy);

        return $this->restoreResource->handle(
            $comment,
            function (Comment $comment) use ($actor, $restoredBy): void {
                $comment->restored_by = $restoredBy;
                $comment->restored_at = now();
                $comment->save();

                $this->auditLogService->record(
                    Log::ACTION_RESTORE_COMMENT,
                    $actor,
                    $comment,
                    ['before' => $this->auditLogService->snapshot($comment)],
                );
            });
    }

    /**
     * Restore multiple soft-deleted comments.
     *
     * @return int Number of comments restored
     *
     * @throws \Exception
     */
    public function restoreMultiple(array $commentIds, int $restoredBy): int
    {
        $count = 0;

        DB::transaction(function () use ($commentIds, $restoredBy, &$count) {
            $actor = User::findOrFail($restoredBy);

            /** @var Collection<int, Comment> $comments */
            $comments = Comment::withTrashed()
                ->whereIn('id', $commentIds)
                ->get();

            foreach ($comments as $comment) {
                if ($comment->trashed()) {
                    $this->restore($comment, $restoredBy, $actor);
                    $count++;
                }
            }
        });

        return $count;
    }
}
