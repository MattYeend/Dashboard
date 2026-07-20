<?php

namespace App\Services\Tags;

use App\Actions\CreateResource;
use App\Models\Log;
use App\Models\Tag;
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
     * Create a new tag.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ModelNotFoundException
     */
    public function create(array $data, int $createdBy): Tag
    {
        $actor = User::findOrFail($createdBy);

        return $this->createResource->handle(
            $data,
            function (array $data) use ($createdBy, $actor): Tag {
                $tagData = $this->dataPreparation->prepareForCreation($data, $createdBy);

                $newTag = Tag::create($tagData);

                $this->auditLogService->record(
                    Log::ACTION_CREATE_TAG,
                    $actor,
                    $newTag,
                    ['after' => $this->auditLogService->snapshot($newTag)],
                );

                return $newTag;
            });
    }
}
