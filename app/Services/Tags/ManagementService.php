<?php

namespace App\Services\Tags;

use App\Http\Requests\Tags\StoreTagRequest;
use App\Http\Requests\Tags\UpdateTagRequest;
use App\Models\Tag;
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
     * Create a new tag.
     */
    public function store(StoreTagRequest $request): Tag
    {
        return $this->creator->create(
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Update an existing tag.
     */
    public function update(UpdateTagRequest $request, Tag $tag): Tag
    {
        return $this->updater->update(
            $tag,
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Soft delete a tag.
     */
    public function destroy(Tag $tag, User $actor): void
    {
        $this->destructor->delete($tag, $actor->id);
    }

    /**
     * Restore a soft-deleted tag.
     */
    public function restore(int $id, User $actor): Tag
    {
        $tag = Tag::withTrashed()->findOrFail($id);

        return $this->restorer->restore($tag, $actor->id);
    }

    /**
     * Force delete a tag, permanently removing it from the database.
     */
    public function forceDelete(int $id, User $actor): void
    {
        $tag = Tag::withTrashed()->findOrFail($id);
        $this->destructor->forceDelete($tag, $actor->id);
    }

    /**
     * Bulk restore tags.
     */
    public function bulkRestore(
        array $ids,
        User $actor,
        callable $authoriseCallback
    ): array {
        $requestedIds = collect($ids)->unique()->values();

        $tags = Tag::onlyTrashed()
            ->whereIn('id', $requestedIds)
            ->get();

        $restored = [];

        foreach ($tags as $tag) {
            /** @var Tag $tag */
            $authoriseCallback($tag);
            $this->restorer->restore($tag, $actor->id);
            $restored[] = $tag->id;
        }

        return [
            'restored' => $restored,
            'skipped' => $requestedIds
                ->diff($tags->pluck('id'))
                ->values()
                ->all(),
        ];
    }

    /**
     * Bulk soft delete tags.
     */
    public function bulkDelete(
        array $ids,
        User $actor,
        callable $authoriseCallback
    ): array {
        $deleted = [];

        foreach ($ids as $id) {
            $tag = Tag::findOrFail($id);
            $authoriseCallback($tag);

            $this->destructor->delete($tag, $actor->id);
            $deleted[] = $id;
        }

        return $deleted;
    }
}
