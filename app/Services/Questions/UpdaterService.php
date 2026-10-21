<?php

declare(strict_types=1);

namespace App\Services\Questions;

use App\Actions\UpdateResource;
use App\Models\Log;
use App\Models\Question;
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
     * Update an existing question.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws \Exception
     */
    public function update(
        Question $question,
        array $data,
        int $updatedBy
    ): Question {
        $actor = User::findOrFail($updatedBy);

        $before = $this->auditLogService->snapshot($question);

        $questionData = $this->dataPreparation->prepareForUpdate($data);

        return $this->updateResource->handle(
            $question,
            $questionData,
            function (Question $question) use ($actor, $before, $updatedBy): void {
                $question->forceFill([
                    'updated_by' => $updatedBy,
                ])->save();

                $fresh = $question->fresh();

                $this->auditLogService->record(
                    Log::ACTION_UPDATE_QUESTION,
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
