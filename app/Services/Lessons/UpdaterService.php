<?php

declare(strict_types=1);

namespace App\Services\Lessons;

use App\Actions\UpdateResource;
use App\Models\Lesson;
use App\Models\Log;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\DB;

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
     * Update an existing lesson.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws \Exception
     */
    public function update(
        Lesson $lesson,
        array $data,
        int $updatedBy
    ): Lesson {
        $actor = User::findOrFail($updatedBy);

        $before = $this->auditLogService->snapshot($lesson);

        return DB::transaction(function () use ($lesson, $data, $actor, $before, $updatedBy): Lesson {
            $lessonData = $this->dataPreparation->prepareForUpdate($data, $lesson);

            return $this->updateResource->handle(
                $lesson,
                $lessonData,
                function (Lesson $lesson) use ($actor, $before, $updatedBy): void {
                    $lesson->forceFill([
                        'updated_by' => $updatedBy,
                    ])->save();
                    $fresh = $lesson->fresh();

                    $this->auditLogService->record(
                        Log::ACTION_UPDATE_LESSON,
                        $actor,
                        $fresh,
                        [
                            'before' => $before,
                            'after' => $this->auditLogService->snapshot($fresh),
                        ],
                    );
                });
        });
    }
}
