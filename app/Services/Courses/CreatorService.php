<?php

declare(strict_types=1);

namespace App\Services\Courses;

use App\Actions\CreateResource;
use App\Models\Course;
use App\Models\Log;
use App\Models\Organisation;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CreatorService
{
    /**
     * Inject the required services into the creator service.
     */
    public function __construct(
        protected readonly DataPreparationService $dataPreparation,
        protected readonly AuditLogService $auditLogService,
        protected readonly CreateResource $createResource,
    ) {}

    /**
     * Create a new course.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ModelNotFoundException
     */
    public function create(array $data, int $createdBy): Course
    {
        $actor = User::findOrFail($createdBy);

        return $this->createResource->handle(
            $data,
            function (array $data) use ($createdBy, $actor): Course {
                $course = Course::create($this->dataPreparation->prepareForCreation($data));

                $course->forceFill([
                    'organisation_id' => Organisation::current()?->id,
                    'created_by' => $createdBy,
                ])->save();

                $this->auditLogService->record(
                    Log::ACTION_CREATE_COURSE,
                    $actor,
                    $course,
                    ['after' => $this->auditLogService->snapshot($course)],
                );

                return $course;
            });
    }
}
