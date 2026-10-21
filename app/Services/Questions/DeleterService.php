<?php

declare(strict_types=1);

namespace App\Services\Questions;

use App\Actions\DeleteResource;
use App\Models\Log;
use App\Models\Question;
use App\Models\User;
use App\Services\AuditLogService;
use DomainException;
use Illuminate\Support\Facades\DB;

class DeleterService
{
    /**
     * Inject the required services into the deleter service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
        protected readonly DeleteResource $deleteResource,
    ) {}

    /**
     * Soft delete a question.
     *
     * @throws \Exception
     */
    public function delete(
        Question $question,
        int $deletedBy,
        ?User $actor = null
    ): bool {
        $actor ??= User::findOrFail($deletedBy);

        return $this->deleteResource->handle(
            $question,
            function (Question $question) use ($actor, $deletedBy): void {
                $question->deleted_by = $deletedBy;
                $question->deleted_at = now();
                $question->save();

                $this->auditLogService->record(
                    Log::ACTION_DELETE_QUESTION,
                    $actor,
                    $question,
                    ['before' => $this->auditLogService->snapshot($question)],
                );
            });
    }

    /**
     * Force delete a question (permanent deletion). Refused while the
     * question is still attached to any quiz.
     *
     * @throws DomainException
     * @throws \Exception
     */
    public function forceDelete(
        Question $question,
        int $deletedBy
    ): bool {
        if ($question->quizzes()->exists()) {
            throw new DomainException(
                'This question is used in one or more quizzes and cannot be permanently deleted.'
            );
        }

        $actor = User::findOrFail($deletedBy);

        return $this->deleteResource->forceHandle(
            $question,
            function (Question $question) use ($actor): void {
                $this->auditLogService->record(
                    Log::ACTION_FORCE_DELETE_QUESTION,
                    $actor,
                    $question,
                    ['before' => $this->auditLogService->snapshot($question)],
                );
            });
    }

    /**
     * Delete multiple questions.
     *
     * @param  array<int, int>  $questionIds
     *
     * @throws \Exception
     */
    public function deleteMultiple(
        array $questionIds,
        int $deletedBy
    ): int {
        $count = 0;

        DB::transaction(function () use ($questionIds, $deletedBy, &$count) {
            $actor = User::findOrFail($deletedBy);
            $questions = Question::whereIn('id', $questionIds)->get();

            foreach ($questions as $question) {
                if ($this->delete($question, $deletedBy, $actor)) {
                    $count++;
                }
            }
        });

        return $count;
    }
}
