<?php

namespace App\Services\Tags;

use App\Actions\DeleteResource;
use App\Models\Log;
use App\Models\Tag;
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
     * Soft delete a tag.
     *
     * @throws \Exception
     */
    public function delete(
        Tag $tag,
        int $deletedBy,
        ?User $actor = null
    ): bool {
        $actor ??= User::findOrFail($deletedBy);

        return $this->deleteResource->handle(
            $tag,
            function (Tag $tag) use ($actor, $deletedBy): void {
                $tag->deleted_by = $deletedBy;
                $tag->deleted_at = now();
                $tag->save();

                $this->auditLogService->record(
                    Log::ACTION_DELETE_TAG,
                    $actor,
                    $tag,
                    ['before' => $this->auditLogService->snapshot($tag)],
                );
            });
    }

    /**
     * Force delete a tag (permanent deletion).
     *
     * @throws \Exception
     */
    public function forceDelete(
        Tag $tag,
        int $deletedBy
    ): bool {
        $actor = User::findOrFail($deletedBy);

        return $this->deleteResource->forceHandle(
            $tag,
            function (Tag $tag) use ($actor): void {
                $this->auditLogService->record(
                    Log::ACTION_FORCE_DELETE_TAG,
                    $actor,
                    $tag,
                    ['before' => $this->auditLogService->snapshot($tag)],
                );
            });
    }

    /**
     * Delete multiple tags.
     *
     * @throws \Exception
     */
    public function deleteMultiple(
        array $tagIds,
        int $deletedBy
    ): int {
        $count = 0;

        DB::transaction(function () use ($tagIds, $deletedBy, &$count) {
            $actor = User::findOrFail($deletedBy);
            $tags = Tag::whereIn('id', $tagIds)->get();

            foreach ($tags as $tag) {
                if ($this->delete($tag, $deletedBy, $actor)) {
                    $count++;
                }
            }
        });

        return $count;
    }
}
