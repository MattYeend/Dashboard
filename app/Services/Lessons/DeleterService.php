<?php

declare(strict_types=1);

namespace App\Services\Lessons;

use App\Actions\DeleteResource;
use App\Models\Lesson;
use App\Models\Log;
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
     * Soft delete a lesson.
     *
     * @throws \Exception
     */
    public function delete(
        Lesson $lesson,
        int $deletedBy,
        ?User $actor = null
    ): bool {
        $actor ??= User::findOrFail($deletedBy);

        return $this->deleteResource->handle(
            $lesson,
            function (Lesson $lesson) use ($actor, $deletedBy): void {
                $lesson->deleted_by = $deletedBy;
                $lesson->deleted_at = now();
                $lesson->save();

                $this->auditLogService->record(
                    Log::ACTION_DELETE_LESSON,
                    $actor,
                    $lesson,
                    ['before' => $this->auditLogService->snapshot($lesson)],
                );
            });
    }

    /**
     * Force delete a lesson (permanent deletion).
     *
     * @throws \Exception
     */
    public function forceDelete(
        Lesson $lesson,
        int $deletedBy
    ): bool {
        $actor = User::findOrFail($deletedBy);

        return $this->deleteResource->forceHandle(
            $lesson,
            function (Lesson $lesson) use ($actor): void {
                $this->auditLogService->record(
                    Log::ACTION_FORCE_DELETE_LESSON,
                    $actor,
                    $lesson,
                    ['before' => $this->auditLogService->snapshot($lesson)],
                );
            });
    }

    /**
     * Delete multiple lessons.
     *
     * @param  array<int, int>  $lessonIds
     *
     * @throws \Exception
     */
    public function deleteMultiple(
        array $lessonIds,
        int $deletedBy
    ): int {
        $count = 0;

        DB::transaction(function () use ($lessonIds, $deletedBy, &$count) {
            $actor = User::findOrFail($deletedBy);
            $lessons = Lesson::whereIn('id', $lessonIds)->get();

            foreach ($lessons as $lesson) {
                if ($this->delete($lesson, $deletedBy, $actor)) {
                    $count++;
                }
            }
        });

        return $count;
    }
}
