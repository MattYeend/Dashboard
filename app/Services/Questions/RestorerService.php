<?php

declare(strict_types=1);

namespace App\Services\Questions;

use App\Actions\RestoreResource;
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
    ) {}

    /**
     * Restore a soft-deleted question.
     *
     * @throws \Exception
     */
    public function restore(
        Question $question,
        int $restoredBy,
        ?User $actor = null
    ): Question {
        $actor ??= User::findOrFail($restoredBy);

        return $this->restoreResource->handle(
            $question,
            function (Question $question) use ($actor, $restoredBy): void {
                $question->restored_by = $restoredBy;
                $question->restored_at = now();
                $question->save();

                $this->auditLogService->record(
                    Log::ACTION_RESTORE_QUESTION,
                    $actor,
                    $question,
                    ['before' => $this->auditLogService->snapshot($question)],
                );
            });
    }

    /**
     * Restore multiple soft-deleted questions.
     *
     * @param  array<int, int>  $questionIds
     * @return int Number of questions restored
     *
     * @throws \Exception
     */
    public function restoreMultiple(
        array $questionIds,
        int $restoredBy
    ): int {
        $count = 0;

        DB::transaction(function () use ($questionIds, $restoredBy, &$count) {
            $actor = User::findOrFail($restoredBy);

            /** @var Collection<int, Question> $questions */
            $questions = Question::withTrashed()
                ->whereIn('id', $questionIds)
                ->get();

            foreach ($questions as $question) {
                if ($question->trashed()) {
                    $this->restore($question, $restoredBy, $actor);
                    $count++;
                }
            }
        });

        return $count;
    }
}
