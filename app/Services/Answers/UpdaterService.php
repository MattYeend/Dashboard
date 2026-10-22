<?php

declare(strict_types=1);

namespace App\Services\Answers;

use App\Actions\Answers\ValidateAnswerSetAction;
use App\Actions\UpdateResource;
use App\Models\Answer;
use App\Models\Log;
use App\Models\Question;
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
        protected readonly ValidateAnswerSetAction $validateSet,
    ) {}

    /**
     * Update an existing answer.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws \Exception
     */
    public function update(
        Answer $answer,
        array $data,
        int $updatedBy
    ): Answer {
        $actor = User::findOrFail($updatedBy);

        return DB::transaction(function () use ($answer, $data, $updatedBy, $actor): Answer {
            $question = Question::query()
                ->lockForUpdate()
                ->findOrFail($answer->question_id);

            $before = $this->auditLogService->snapshot($answer);

            $answerData = $this->dataPreparation->prepareForUpdate($data);

            return $this->updateResource->handle(
                $answer,
                $answerData,
                function (Answer $answer) use ($actor, $before, $updatedBy, $question): void {
                    $answer->forceFill([
                        'updated_by' => $updatedBy,
                    ])->save();

                    $this->validateSet->assertNotInvalid($question);

                    $fresh = $answer->fresh();

                    $this->auditLogService->record(
                        Log::ACTION_UPDATE_ANSWER,
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
