<?php

declare(strict_types=1);

namespace App\Services\Quizzes;

use App\Actions\UpdateResource;
use App\Models\Log;
use App\Models\Quiz;
use App\Models\User;
use App\Services\AuditLogService;

class UpdaterService
{
    /**
     * Inject the required services into the updater service.
     */
    public function __construct(
        protected readonly DataPreparationService $dataPreparation,
        protected readonly QuestionAttachmentService $questionAttachment,
        protected readonly AuditLogService $auditLogService,
        protected readonly UpdateResource $updateResource,
    ) {}

    /**
     * Update an existing quiz and, when supplied, re-sync its questions.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws \Exception
     */
    public function update(
        Quiz $quiz,
        array $data,
        int $updatedBy
    ): Quiz {
        $actor = User::findOrFail($updatedBy);

        $before = $this->auditLogService->snapshot($quiz);

        $quizData = $this->dataPreparation->prepareForUpdate($data, $quiz->id, $quiz->title);
        $selections = $this->dataPreparation->extractQuestionSelections($data);

        return $this->updateResource->handle(
            $quiz,
            $quizData,
            function (Quiz $quiz) use ($actor, $before, $selections, $updatedBy): void {
                $quiz->forceFill(['updated_by' => $updatedBy])->save();

                if ($selections !== null) {
                    $this->questionAttachment->sync($quiz, $selections);
                }

                $fresh = $quiz->fresh();

                $this->auditLogService->record(
                    Log::ACTION_UPDATE_QUIZ,
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
