<?php

namespace App\Services\Posts;

use App\Actions\UpdateResource;
use App\Models\Log;
use App\Models\Post;
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
     * Update an existing post.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws \Exception
     */
    public function update(
        Post $post,
        array $data,
        int $updatedBy
    ): Post {
        $actor = User::findOrFail($updatedBy);

        $before = $this->auditLogService->snapshot($post);

        $orderStatusData = $this->dataPreparation->prepareForUpdate($data, $updatedBy);

        return $this->updateResource->handle(
            $post,
            $orderStatusData,
            function (Post $post) use ($actor, $before): void {
                $fresh = $post->fresh();

                $this->auditLogService->record(
                    Log::ACTION_UPDATE_POST,
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
