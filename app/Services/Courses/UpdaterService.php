<?php

declare(strict_types=1);

namespace App\Services\Courses;

use App\Actions\UpdateResource;
use App\Models\Course;
use App\Models\Log;
use App\Models\User;
use App\Services\AuditLogService;

class UpdaterService
{
    /**
     * Inject the required services into the updater service.
     */
    public function __construct(
        protected readonly DataPreparationService $dataPreparation,
        protected readonly AuditLogService $auditLogService,
        protected readonly UpdateResource $updateResource,
    ) {}

    /**
     * Update an existing course.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws \Exception
     */
    public function update(
        Course $course,
        array $data,
        int $updatedBy
    ): Course {
        $actor = User::findOrFail($updatedBy);

        $before = $this->auditLogService->snapshot($course);

        $courseData = $this->dataPreparation->prepareForUpdate($course, $data);

        return $this->updateResource->handle(
            $course,
            $courseData,
            function (Course $course) use ($actor, $before, $updatedBy): void {
                $course->forceFill([
                    'updated_by' => $updatedBy,
                ])->save();

                $fresh = $course->fresh();

                $this->auditLogService->record(
                    Log::ACTION_UPDATE_COURSE,
                    $actor,
                    $fresh,
                    [
                        'before' => $before,
                        'after' => $this->auditLogService->snapshot($fresh),
                    ],
                );
            });
    }
}
