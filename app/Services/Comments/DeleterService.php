<?php

namespace App\Services\Comments;

use App\Actions\DeleteResource;
use App\Models\Comment;
use App\Models\Log;
use App\Models\User;
use App\Services\AuditLogService;

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
        int $deletedBy
    ): bool {
        $actor = User::findOrFail(
            $deletedBy
        );

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
                    [
                        'before' => $this->auditLogService->snapshot(
                            $comment
                        ),
                    ],
                );
            }
        );
    }
}
