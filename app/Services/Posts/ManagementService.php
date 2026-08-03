<?php

namespace App\Services\Posts;

use App\Actions\Like\LikePost;
use App\Actions\Like\UnlikePost;
use App\Http\Requests\Posts\ImportPostRequest;
use App\Http\Requests\Posts\StorePostRequest;
use App\Http\Requests\Posts\UpdatePostRequest;
use App\Models\Post;
use App\Models\User;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
        protected readonly LikePost $likePost,
        protected readonly UnlikePost $unlikePost,
        protected readonly ImporterService $importer,
        protected readonly ExporterService $exporter,
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
        $requestedIds = collect($ids)->unique()->values();

        $posts = Post::whereIn('id', $requestedIds)->get();

        $deleted = [];

        foreach ($posts as $post) {
            /** @var Post $post */
            $authoriseCallback($post);

            $this->destructor->delete($post, $actor->id);

            $deleted[] = $post->id;
        }

        return [
            'deleted' => $deleted,
            'skipped' => $requestedIds
                ->diff($posts->pluck('id'))
                ->values()
                ->all(),
        ];
    }

    /**
     * Like the given post on behalf of the given user.
     */
    public function like(
        Post $post,
        User $actor
    ): void {
        $this->likePost->handle($post, $actor);
    }

    /**
     * Unlike the given post on behalf of the given user.
     */
    public function unlike(
        Post $post,
        User $actor
    ): void {
        $this->unlikePost->handle($post, $actor);
    }

    /**
     * Import posts from an uploaded file.
     *
     * @return array{imported: int, skipped: array<int, array{row: int, reason: string}>}
     */
    public function import(
        ImportPostRequest $request
    ): array {
        return $this->importer->import(
            $request->file('file'),
            $request->user()->id
        );
    }

    /**
     * Export posts matching the given filters as a CSV download.
     *
     * @param  array<string, mixed>  $filters
     */
    public function export(array $filters): StreamedResponse
    {
        return $this->exporter->export($filters);
    }
}
