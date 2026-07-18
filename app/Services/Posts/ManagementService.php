<?php

namespace App\Services\Posts;

use App\Http\Requests\Posts\StorePostRequest;
use App\Http\Requests\Posts\UpdatePostRequest;
use App\Models\Post;
use App\Models\User;

class ManagementService
{
    /**
     * Inject the required services into the management service.
     */
    public function __construct(
        protected readonly CreatorService $creator,
        protected readonly UpdaterService $updater,
        protected readonly DeleterService $destructor,
        protected readonly RestorerService $restorer,
    ) {}

    /**
     * Create a new order status.
     */
    public function store(
        StorePostRequest $request
    ): Post {
        return $this->creator->create(
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Update an existing order status.
     */
    public function update(
        UpdatePostRequest $request,
        Post $post
    ): Post {
        return $this->updater->update(
            $post,
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Soft delete a order status.
     */
    public function destroy(
        Post $post,
        User $actor
    ): void {
        $this->destructor->delete($post, $actor->id);
    }

    /**
     * Restore a soft-deleted order status.
     */
    public function restore(
        int $id,
        User $actor
    ): Post {
        $post = Post::withTrashed()->findOrFail($id);

        return $this->restorer->restore($post, $actor->id);
    }

    /**
     * Force delete a order status, permanently removing it from the
     * database.
     */
    public function forceDelete(
        int $id,
        User $actor
    ): void {
        $post = Post::withTrashed()->findOrFail($id);
        $this->destructor->forceDelete($post, $actor->id);
    }

    /**
     * Bulk restore order statuses.
     */
    public function bulkRestore(
        array $ids,
        User $actor,
        callable $authoriseCallback
    ): array {
        $requestedIds = collect($ids)->unique()->values();

        $posts = Post::onlyTrashed()
            ->whereIn('id', $requestedIds)
            ->get();

        $restored = [];

        foreach ($posts as $post) {
            /** @var Post $post */
            $authoriseCallback($post);
            $this->restorer->restore($post, $actor->id);
            $restored[] = $post->id;
        }

        return [
            'restored' => $restored,
            'skipped' => $requestedIds
                ->diff($posts->pluck('id'))
                ->values()
                ->all(),
        ];
    }

    /**
     * Bulk soft delete order statuses.
     */
    public function bulkDelete(
        array $ids,
        User $actor,
        callable $authoriseCallback
    ): array {
        $deleted = [];

        foreach ($ids as $id) {
            $post = Post::findOrFail($id);
            $authoriseCallback($post);

            $this->destructor->delete($post, $actor->id);
            $deleted[] = $id;
        }

        return $deleted;
    }
}
