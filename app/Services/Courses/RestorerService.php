<?php

declare(strict_types=1);

namespace App\Services\Courses;

use App\Actions\RestoreResource;
use App\Models\Course;
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
     * Restore a soft-deleted course.
     *
     * @throws \Exception
     */
    public function restore(
        Course $course,
        int $restoredBy,
        ?User $actor = null
    ): Course {
        $actor ??= User::findOrFail($restoredBy);

        return $this->restoreResource->handle(
            $course,
            function (Course $course) use ($actor, $restoredBy): void {
                $course->restored_by = $restoredBy;
                $course->restored_at = now();
                $course->save();

                $this->auditLogService->record(
                    Log::ACTION_RESTORE_COURSE,
                    $actor,
                    $course,
                    ['before' => $this->auditLogService->snapshot($course)],
                );
            });
    }

    /**
     * Restore multiple soft-deleted courses.
     *
     * @param  array<int, int>  $courseIds
     * @return int Number of courses restored
     *
     * @throws \Exception
     */
    public function restoreMultiple(array $courseIds, int $restoredBy): int
    {
        $count = 0;

        DB::transaction(function () use ($courseIds, $restoredBy, &$count) {
            $actor = User::findOrFail($restoredBy);

            /** @var Collection<int, Course> $courses */
            $courses = Course::onlyTrashed()
                ->whereIn('id', $courseIds)
                ->get();

            foreach ($courses as $course) {
                $this->restore($course, $restoredBy, $actor);
                $count++;
            }
        });

        return $count;
    }
}
