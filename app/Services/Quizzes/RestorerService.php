<?php

declare(strict_types=1);

namespace App\Services\Quizzes;

use App\Actions\RestoreResource;
use App\Models\Log;
use App\Models\Quiz;
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
     * Restore a soft-deleted quiz.
     *
     * @throws \Exception
     */
    public function restore(
        Quiz $quiz,
        int $restoredBy,
        ?User $actor = null,
    ): Quiz {
        $actor ??= User::findOrFail($restoredBy);

        return $this->restoreResource->handle(
            $quiz,
            function (Quiz $quiz) use ($actor, $restoredBy): void {
                $quiz->restored_by = $restoredBy;
                $quiz->restored_at = now();
                $quiz->save();

                $this->auditLogService->record(
                    Log::ACTION_RESTORE_QUIZ,
                    $actor,
                    $quiz,
                    ['before' => $this->auditLogService->snapshot($quiz)],
                );
            });
    }

    /**
     * Restore multiple soft-deleted quizzes.
     *
     * @param  array<int, int>  $quizIds
     * @return int Number of quizzes restored
     *
     * @throws \Exception
     */
    public function restoreMultiple(
        array $quizIds,
        int $restoredBy
    ): int {
        $count = 0;

        DB::transaction(function () use ($quizIds, $restoredBy, &$count) {
            $actor = User::findOrFail($restoredBy);

            /** @var Collection<int, Quiz> $quizzes */
            $quizzes = Quiz::onlyTrashed()->whereIn('id', $quizIds)->get();

            foreach ($quizzes as $quiz) {
                $this->restore($quiz, $restoredBy, $actor);
                $count++;
            }
        });

        return $count;
    }
}
