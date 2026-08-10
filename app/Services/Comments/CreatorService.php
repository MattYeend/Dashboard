<?php

namespace App\Services\Comments;

use App\Actions\CreateResource;
use App\Models\Comment;
use App\Models\Log;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CreatorService
{
    /**
     * Inject the required services into the creator service.
     */
    public function __construct(
        protected readonly DataPreparationService $dataPreparation,
        protected readonly AuditLogService $auditLogService,
        protected readonly CreateResource $createResource,
    ) {}

    /**
     * Create a new comment.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ModelNotFoundException
     */
    public function create(array $data, int $createdBy): Comment
    {
        $actor = User::findOrFail($createdBy);

        return $this->createResource->handle(
            $data,
            function (array $data) use ($createdBy, $actor): Comment {
                $commentData = $this->dataPreparation->prepareForCreation($data, $createdBy);

                $newComment = Comment::create($commentData);

                $this->auditLogService->record(
                    Log::ACTION_CREATE_COMMENT,
                    $actor,
                    $newComment,
                    ['after' => $this->auditLogService->snapshot($newComment)],
                );

                return $newComment;
            });
    }
}
