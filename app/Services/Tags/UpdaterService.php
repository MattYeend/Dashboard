<?php

namespace App\Services\Tags;

use App\Actions\UpdateResource;
use App\Models\Log;
use App\Models\Tag;
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
     * Update an existing tag.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws \Exception
     */
    public function update(
        Tag $tag,
        array $data,
        int $updatedBy
    ): Tag {
        $actor = User::findOrFail($updatedBy);

        $before = $this->auditLogService->snapshot($tag);

        $tagData = $this->dataPreparation->prepareForUpdate($data, $updatedBy);

        return $this->updateResource->handle(
            $tag,
            $tagData,
            function (Tag $tag) use ($actor, $before): void {
                $fresh = $tag->fresh();

                $this->auditLogService->record(
                    Log::ACTION_UPDATE_TAG,
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
