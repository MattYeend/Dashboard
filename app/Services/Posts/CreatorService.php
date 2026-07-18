<?php

namespace App\Services\Posts;

use App\Actions\CreateResource;
use App\Models\Log;
use App\Models\Post;
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
     * Create a new post.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ModelNotFoundException
     */
    public function create(array $data, int $createdBy): Post
    {
        $actor = User::findOrFail($createdBy);

        return $this->createResource->handle(
            $data,
            function (array $data) use ($createdBy, $actor): Post {
                $postData = $this->dataPreparation->prepareForCreation($data, $createdBy);

                $newPost = Post::create($postData);

                $this->auditLogService->record(
                    Log::ACTION_CREATE_POST,
                    $actor,
                    $newPost,
                    ['after' => $this->auditLogService->snapshot($newPost)],
                );

                return $newPost;
            });
    }
}
