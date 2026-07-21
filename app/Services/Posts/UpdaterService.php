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

        $postData = $this->dataPreparation->prepareForUpdate($data, $updatedBy);

        return $this->updateResource->handle(
            $post,
            $postData,
            function (Post $post) use ($actor, $before, $data): void {
                if (array_key_exists('category_ids', $data)) {
                    $post->categories()->sync($data['category_ids'] ?? []);
                }

                if (array_key_exists('tag_ids', $data)) {
                    $post->tags()->sync($data['tag_ids'] ?? []);
                }

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
