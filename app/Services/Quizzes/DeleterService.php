<?php

declare(strict_types=1);

namespace App\Services\Quizzes;

use App\Actions\DeleteResource;
use App\Models\Log;
use App\Models\Quiz;
use App\Models\User;
use App\Services\AuditLogService;
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
     * Soft delete a quiz.
     *
     * @throws \Exception
     */
    public function delete(
        Quiz $quiz,
        int $deletedBy,
        ?User $actor = null
    ): bool {
        $actor ??= User::findOrFail($deletedBy);

        return $this->deleteResource->handle(
            $quiz,
            function (Quiz $quiz) use ($actor, $deletedBy): void {
                $quiz->deleted_by = $deletedBy;
                $quiz->deleted_at = now();
                $quiz->save();

                $this->auditLogService->record(
                    Log::ACTION_DELETE_QUIZ,
                    $actor,
                    $quiz,
                    ['before' => $this->auditLogService->snapshot($quiz)],
                );
            });
    }

    /**
     * Force delete a quiz (permanent deletion).
     *
     * @throws \Exception
     */
    public function forceDelete(
        Quiz $quiz,
        int $deletedBy
    ): bool {
        $actor = User::findOrFail($deletedBy);

        return $this->deleteResource->forceHandle(
            $quiz,
            function (Quiz $quiz) use ($actor): void {
                $this->auditLogService->record(
                    Log::ACTION_FORCE_DELETE_QUIZ,
                    $actor,
                    $quiz,
                    ['before' => $this->auditLogService->snapshot($quiz)],
                );
            });
    }

    /**
     * Delete multiple quizzes.
     *
     * @param  array<int, int>  $quizIds
     *
     * @throws \Exception
     */
    public function deleteMultiple(
        array $quizIds,
        int $deletedBy
    ): int {
        $count = 0;

        DB::transaction(function () use ($quizIds, $deletedBy, &$count) {
            $actor = User::findOrFail($deletedBy);

            foreach (Quiz::whereIn('id', $quizIds)->get() as $quiz) {
                if ($this->delete($quiz, $deletedBy, $actor)) {
                    $count++;
                }
            }
        });

        return $count;
    }
}
