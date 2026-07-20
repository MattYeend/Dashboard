<?php

namespace App\Services\Tags;

use App\Actions\RestoreResource;
use App\Models\Log;
use App\Models\Tag;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class RestorerService
{
    /**
     * Inject the required services into the restorer service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
        protected readonly RestoreResource $restoreResource,
    ) {}

    /**
     * Restore a soft-deleted tag.
     *
     * @throws \Exception
     */
    public function restore(
        Tag $tag,
        int $restoredBy,
        ?User $actor = null
    ): Tag {
        $actor ??= User::findOrFail($restoredBy);

        return $this->restoreResource->handle(
            $tag,
            function (Tag $tag) use ($actor, $restoredBy): void {
                $tag->restored_by = $restoredBy;
                $tag->restored_at = now();
                $tag->save();

                $this->auditLogService->record(
                    Log::ACTION_RESTORE_TAG,
                    $actor,
                    $tag,
                    ['before' => $this->auditLogService->snapshot($tag)],
                );
            });
    }

    /**
     * Restore multiple soft-deleted tags.
     *
     * @return int Number of tags restored
     *
     * @throws \Exception
     */
    public function restoreMultiple(
        array $tagIds,
        int $restoredBy
    ): int {
        $count = 0;

        DB::transaction(function () use ($tagIds, $restoredBy, &$count) {
            $actor = User::findOrFail($restoredBy);

            /** @var Collection<int,Tag> $tags */
            $tags = Tag::withTrashed()
                ->whereIn('id', $tagIds)
                ->get();

            foreach ($tags as $tag) {
                if ($tag->trashed()) {
                    $this->restore($tag, $restoredBy, $actor);
                    $count++;
                }
            }
        });

        return $count;
    }
}
