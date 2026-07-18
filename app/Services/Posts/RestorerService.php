<?php

namespace App\Services\Posts;

use App\Actions\RestoreResource;
use App\Models\Log;
use App\Models\Post;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class RestorerService
{
    /**
     * Inject the required services into the resorer service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
        protected readonly RestoreResource $restoreResource,
    ) {}

    /**
     * Restore a soft-deleted post.
     *
     * @throws \Exception
     */
    public function restore(
        Post $post,
        int $restoredBy,
        ?User $actor = null
    ): Post {
        $actor ??= User::findOrFail($restoredBy);

        return $this->restoreResource->handle(
            $post,
            function (Post $post) use ($actor, $restoredBy): void {
                $post->restored_by = $restoredBy;
                $post->restored_at = now();
                $post->save();

                $this->auditLogService->record(
                    Log::ACTION_RESTORE_POST,
                    $actor,
                    $post,
                    ['before' => $this->auditLogService->snapshot($post)],
                );
            });
    }

    /**
     * Restore multiple soft-deleted posts.
     *
     * @return int Number of posts restored
     *
     * @throws \Exception
     */
    public function restoreMultiple(
        array $postIds,
        int $restoredBy
    ): int {
        $count = 0;

        DB::transaction(function () use ($postIds, $restoredBy, &$count) {
            $actor = User::findOrFail($restoredBy);

            /** @var Collection<int,Post> $posts */
            $posts = Post::withTrashed()
                ->whereIn('id', $postIds)
                ->get();

            foreach ($posts as $post) {
                if ($post->trashed()) {
                    $this->restore($post, $restoredBy, $actor);
                    $count++;
                }
            }
        });

        return $count;
    }
}
