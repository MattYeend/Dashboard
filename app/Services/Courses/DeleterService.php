<?php

declare(strict_types=1);

namespace App\Services\Courses;

use App\Actions\DeleteResource;
use App\Models\Course;
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
     * Soft delete a course.
     *
     * @throws \Exception
     */
    public function delete(Course $course, int $deletedBy, ?User $actor = null): bool
    {
        $actor ??= User::findOrFail($deletedBy);

        return $this->deleteResource->handle(
            $course,
            function (Course $course) use ($actor, $deletedBy): void {
                $course->deleted_by = $deletedBy;
                $course->deleted_at = now();
                $course->save();

                $this->auditLogService->record(
                    Log::ACTION_DELETE_COURSE,
                    $actor,
                    $course,
                    ['before' => $this->auditLogService->snapshot($course)],
                );
            });
    }

    /**
     * Force delete a course (permanent deletion).
     *
     * @throws \Exception
     */
    public function forceDelete(Course $course, int $deletedBy): bool
    {
        $actor = User::findOrFail($deletedBy);

        return $this->deleteResource->forceHandle(
            $course,
            function (Course $course) use ($actor): void {
                $this->auditLogService->record(
                    Log::ACTION_FORCE_DELETE_COURSE,
                    $actor,
                    $course,
                    ['before' => $this->auditLogService->snapshot($course)],
                );
            });
    }

    /**
     * Delete multiple courses.
     *
     * @param  array<int, int>  $courseIds
     *
     * @throws \Exception
     */
    public function deleteMultiple(array $courseIds, int $deletedBy): int
    {
        $count = 0;

        DB::transaction(function () use ($courseIds, $deletedBy, &$count) {
            $actor = User::findOrFail($deletedBy);
            $courses = Course::whereIn('id', $courseIds)->get();

            foreach ($courses as $course) {
                if ($this->delete($course, $deletedBy, $actor)) {
                    $count++;
                }
            }
        });

        return $count;
    }
}
