<?php

namespace App\Services\Posts;

use App\Actions\DeleteResource;
use App\Models\Log;
use App\Models\Post;
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
     * Soft delete a post.
     *
     * @throws \Exception
     */
    public function delete(
        Post $post,
        int $deletedBy,
        ?User $actor = null
    ): bool {
        $actor ??= User::findOrFail($deletedBy);

        return $this->deleteResource->handle(
            $post,
            function (Post $post) use ($actor, $deletedBy): void {
                $post->deleted_by = $deletedBy;
                $post->deleted_at = now();
                $post->save();

                $this->auditLogService->record(
                    Log::ACTION_DELETE_POST,
                    $actor,
                    $post,
                    ['before' => $this->auditLogService->snapshot($post)],
                );
            });
    }

    /**
     * Force delete a post (permanent deletion).
     *
     * @throws \Exception
     */
    public function forceDelete(
        Post $post,
        int $deletedBy
    ): bool {
        $actor = User::findOrFail($deletedBy);

        return $this->deleteResource->forceHandle(
            $post,
            function (Post $post) use ($actor): void {
                $this->auditLogService->record(
                    Log::ACTION_FORCE_DELETE_POST,
                    $actor,
                    $post,
                    ['before' => $this->auditLogService->snapshot($post)],
                );
            });
    }

    /**
     * Delete multiple posts.
     *
     * @throws \Exception
     */
    public function deleteMultiple(
        array $postIds,
        int $deletedBy
    ): int {
        $count = 0;

        DB::transaction(function () use ($postIds, $deletedBy, &$count) {
            $actor = User::findOrFail($deletedBy);
            $posts = Post::whereIn('id', $postIds)->get();

            foreach ($posts as $post) {
                if ($this->delete($post, $deletedBy, $actor)) {
                    $count++;
                }
            }
        });

        return $count;
    }
}
