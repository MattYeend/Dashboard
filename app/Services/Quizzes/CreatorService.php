<?php

declare(strict_types=1);

namespace App\Services\Quizzes;

use App\Actions\CreateResource;
use App\Models\Log;
use App\Models\Quiz;
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
        protected readonly QuestionAttachmentService $questionAttachment,
        protected readonly AuditLogService $auditLogService,
        protected readonly CreateResource $createResource,
    ) {}

    /**
     * Create a new quiz and attach its questions.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ModelNotFoundException
     */
    public function create(array $data, int $createdBy): Quiz
    {
        $actor = User::findOrFail($createdBy);

        return $this->createResource->handle(
            $data,
            function (array $data) use ($createdBy, $actor): Quiz {
                $attributes = $this->dataPreparation->prepareForCreation($data, $createdBy);
                $selections = $this->dataPreparation->extractQuestionSelections($data);

                $quiz = (new Quiz)->forceFill($attributes);
                $quiz->save();

                if ($selections !== null) {
                    $this->questionAttachment->sync($quiz, $selections);
                }

                $this->auditLogService->record(
                    Log::ACTION_CREATE_QUIZ,
                    $actor,
                    $quiz,
                    ['after' => $this->auditLogService->snapshot($quiz)],
                );

                return $quiz;
            });
    }
}
