<?php

declare(strict_types=1);

namespace App\Services\Answers;

use App\Actions\Answers\ValidateAnswerSetAction;
use App\Actions\CreateResource;
use App\Models\Answer;
use App\Models\Log;
use App\Models\Question;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreatorService
{
    /**
     * Inject the required services into the creator service.
     */
    public function __construct(
        protected readonly DataPreparationService $dataPreparation,
        protected readonly AuditLogService $auditLogService,
        protected readonly CreateResource $createResource,
        protected readonly ValidateAnswerSetAction $validateSet,
    ) {}

    /**
     * Create a new answer.
     *
     * The parent question row is locked for the duration of the transaction
     * so concurrent requests cannot break the answer set rules.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ModelNotFoundException
     * @throws ValidationException
     */
    public function create(array $data, int $createdBy): Answer
    {
        $actor = User::findOrFail($createdBy);

        return DB::transaction(function () use ($data, $createdBy, $actor): Answer {
            $question = Question::query()
                ->lockForUpdate()
                ->findOrFail($data['question_id']);

            $data['position'] ??= $this->nextPosition($question);

            return $this->createResource->handle(
                $data,
                function (array $data) use ($createdBy, $actor, $question): Answer {
                    $answerData = $this->dataPreparation->prepareForCreation($data);

                    $newAnswer = Answer::create($answerData);

                    $newAnswer->forceFill([
                        'created_by' => $createdBy,
                    ])->save();

                    $this->validateSet->assertNotInvalid($question);

                    $this->auditLogService->record(
                        Log::ACTION_CREATE_ANSWER,
                        $actor,
                        $newAnswer,
                        ['after' => $this->auditLogService->snapshot($newAnswer)],
                    );

                    return $newAnswer;
                });
        });
    }

    /**
     * Get the next free position for a question, including deleted answers.
     */
    protected function nextPosition(Question $question): int
    {
        return (int) Answer::withTrashed()
            ->where('question_id', $question->id)
            ->max('position') + 1;
    }
}
