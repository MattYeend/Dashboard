<?php

namespace App\Services\Comments;

use App\Actions\UpdateResource;
use App\Models\Comment;
use App\Models\Log;
use App\Models\User;
use App\Services\AuditLogService;

class UpdaterService
{
    /**
     * Inject the required services into the updater service.
     */
    public function __construct(
        protected readonly DataPreparationService $dataPreparation,
        protected readonly AuditLogService $auditLogService,
        protected readonly UpdateResource $updateResource,
    ) {}

    /**
     * Update an existing comment.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws \Exception
     */
    public function update(
        Comment $comment,
        array $data,
        int $updatedBy
    ): Comment {
        $actor = User::findOrFail($updatedBy);

        $before = $this->auditLogService->snapshot($comment);

        $commentData = $this->dataPreparation->prepareForUpdate(
            $data,
            $updatedBy
        );

        return $this->updateResource->handle(
            $comment,
            $commentData,
            function (Comment $comment) use ($actor, $before): void {
                $fresh = $comment->fresh();

                $this->auditLogService->record(
                    Log::ACTION_UPDATE_COMMENT,
                    $actor,
                    $fresh,
                    [
                        'before' => $before,
                        'after' => $this->auditLogService->snapshot($fresh),
                    ],
                );
            });
    }
}
