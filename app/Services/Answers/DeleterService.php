<?php

declare(strict_types=1);

namespace App\Services\Answers;

use App\Actions\DeleteResource;
use App\Models\Answer;
use App\Models\Log;
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
     * Soft delete an answer.
     *
     * @throws \Exception
     */
    public function delete(
        Answer $answer,
        int $deletedBy,
        ?User $actor = null
    ): bool {
        $actor ??= User::findOrFail($deletedBy);

        return $this->deleteResource->handle(
            $answer,
            function (Answer $answer) use ($actor, $deletedBy): void {
                $answer->deleted_by = $deletedBy;
                $answer->deleted_at = now();
                $answer->save();

                $this->auditLogService->record(
                    Log::ACTION_DELETE_ANSWER,
                    $actor,
                    $answer,
                    ['before' => $this->auditLogService->snapshot($answer)],
                );
            });
    }

    /**
     * Force delete an answer (permanent deletion).
     *
     * @throws \Exception
     */
    public function forceDelete(Answer $answer, int $deletedBy): bool
    {
        $actor = User::findOrFail($deletedBy);

        return $this->deleteResource->forceHandle(
            $answer,
            function (Answer $answer) use ($actor): void {
                $this->auditLogService->record(
                    Log::ACTION_FORCE_DELETE_ANSWER,
                    $actor,
                    $answer,
                    ['before' => $this->auditLogService->snapshot($answer)],
                );
            });
    }

    /**
     * Delete multiple answers.
     *
     * @param  array<int, int>  $answerIds
     *
     * @throws \Exception
     */
    public function deleteMultiple(array $answerIds, int $deletedBy): int
    {
        $count = 0;

        DB::transaction(function () use ($answerIds, $deletedBy, &$count) {
            $actor = User::findOrFail($deletedBy);
            $answers = Answer::whereIn('id', $answerIds)->get();

            foreach ($answers as $answer) {
                if ($this->delete($answer, $deletedBy, $actor)) {
                    $count++;
                }
            }
        });

        return $count;
    }
}
