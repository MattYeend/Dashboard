<?php

declare(strict_types=1);

namespace App\Services\Answers;

use App\Actions\Answers\ValidateAnswerSetAction;
use App\Actions\RestoreResource;
use App\Models\Answer;
use App\Models\Log;
use App\Models\Question;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class RestorerService
{
    /**
     * Inject the required services into the restorer service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
        protected readonly RestoreResource $restoreResource,
        protected readonly ValidateAnswerSetAction $validateSet,
    ) {}

    /**
     * Restore a soft-deleted answer.
     *
     * Restoring can bring an answer set back over its limits, so the set
     * rules are checked under the same lock as any other write.
     *
     * @throws \Exception
     */
    public function restore(
        Answer $answer,
        int $restoredBy,
        ?User $actor = null
    ): Answer {
        $actor ??= User::findOrFail($restoredBy);

        return DB::transaction(function () use ($answer, $restoredBy, $actor): Answer {
            $question = Question::query()
                ->lockForUpdate()
                ->findOrFail($answer->question_id);

            return $this->restoreResource->handle(
                $answer,
                function (Answer $answer) use ($actor, $restoredBy, $question): void {
                    $answer->restored_by = $restoredBy;
                    $answer->restored_at = now();
                    $answer->save();

                    $this->validateSet->assertNotInvalid($question);

                    $this->auditLogService->record(
                        Log::ACTION_RESTORE_ANSWER,
                        $actor,
                        $answer,
                        ['before' => $this->auditLogService->snapshot($answer)],
                    );
                });
        });
    }

    /**
     * Restore multiple soft-deleted answers.
     *
     * @param  array<int, int>  $answerIds
     * @return int Number of answers restored
     *
     * @throws \Exception
     */
    public function restoreMultiple(array $answerIds, int $restoredBy): int
    {
        $count = 0;

        DB::transaction(function () use ($answerIds, $restoredBy, &$count) {
            $actor = User::findOrFail($restoredBy);

            /** @var Collection<int, Answer> $answers */
            $answers = Answer::withTrashed()
                ->whereIn('id', $answerIds)
                ->get();

            foreach ($answers as $answer) {
                if ($answer->trashed()) {
                    $this->restore($answer, $restoredBy, $actor);
                    $count++;
                }
            }
        });

        return $count;
    }
}
