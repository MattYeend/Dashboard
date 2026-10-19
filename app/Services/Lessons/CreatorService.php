<?php

declare(strict_types=1);

namespace App\Services\Lessons;

use App\Actions\CreateResource;
use App\Models\Lesson;
use App\Models\Log;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

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
     * Create a new lesson.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ModelNotFoundException
     */
    public function create(array $data, int $createdBy): Lesson
    {
        $actor = User::findOrFail($createdBy);

        return DB::transaction(fn (): Lesson => $this->createResource->handle(
            $data,
            function (array $data) use ($createdBy, $actor): Lesson {
                $lesson = new Lesson;

                // forceFill is safe here: every key comes from the
                // allow-list built in prepareForCreation().
                $lesson->forceFill(array_merge(
                    $this->dataPreparation->prepareForCreation($data),
                    ['created_by' => $createdBy],
                ))->save();

                $this->auditLogService->record(
                    Log::ACTION_CREATE_LESSON,
                    $actor,
                    $lesson,
                    ['after' => $this->auditLogService->snapshot($lesson)],
                );

                return $lesson;
            }));
    }
}
