<?php

declare(strict_types=1);

namespace App\Services\Lessons;

use App\Actions\RestoreResource;
use App\Models\Lesson;
use App\Models\Log;
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
     * Restore a soft-deleted lesson.
     *
     * @throws \Exception
     */
    public function restore(
        Lesson $lesson,
        int $restoredBy,
        ?User $actor = null
    ): Lesson {
        $actor ??= User::findOrFail($restoredBy);

        return $this->restoreResource->handle(
            $lesson,
            function (Lesson $lesson) use ($actor, $restoredBy): void {
                $lesson->restored_by = $restoredBy;
                $lesson->restored_at = now();
                $lesson->save();

                $this->auditLogService->record(
                    Log::ACTION_RESTORE_LESSON,
                    $actor,
                    $lesson,
                    ['before' => $this->auditLogService->snapshot($lesson)],
                );
            });
    }

    /**
     * Restore multiple soft-deleted lessons.
     *
     * @param  array<int, int>  $lessonIds
     * @return int Number of lessons restored
     *
     * @throws \Exception
     */
    public function restoreMultiple(
        array $lessonIds,
        int $restoredBy
    ): int {
        $count = 0;

        DB::transaction(function () use ($lessonIds, $restoredBy, &$count) {
            $actor = User::findOrFail($restoredBy);

            /** @var Collection<int,Lesson> $lessons */
            $lessons = Lesson::withTrashed()
                ->whereIn('id', $lessonIds)
                ->get();

            foreach ($lessons as $lesson) {
                if ($lesson->trashed()) {
                    $this->restore($lesson, $restoredBy, $actor);
                    $count++;
                }
            }
        });

        return $count;
    }
}
