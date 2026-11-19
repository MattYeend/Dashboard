<?php

declare(strict_types=1);

namespace App\Services\TaskStatuses;

use App\Actions\DeleteResource;
use App\Models\Log;
use App\Models\TaskStatus;
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
     * Soft delete a task status.
     *
     * @throws \DomainException When the status is a protected system status.
     * @throws \Exception
     */
    public function delete(
        TaskStatus $taskStatus,
        int $deletedBy,
        ?User $actor = null
    ): bool {
        $this->ensureNotSystem($taskStatus);

        $actor ??= User::findOrFail($deletedBy);

        return $this->deleteResource->handle(
            $taskStatus,
            function (TaskStatus $taskStatus) use ($actor, $deletedBy): void {
                $taskStatus->deleted_by = $deletedBy;
                $taskStatus->deleted_at = now();
                $taskStatus->save();

                $this->auditLogService->record(
                    Log::ACTION_DELETE_TASK_STATUS,
                    $actor,
                    $taskStatus,
                    ['before' => $this->auditLogService->snapshot($taskStatus)],
                );
            });
    }

    /**
     * Force delete a task status (permanent deletion).
     *
     * @throws \DomainException When the status is a protected system status.
     * @throws \Exception
     */
    public function forceDelete(
        TaskStatus $taskStatus,
        int $deletedBy
    ): bool {
        $this->ensureNotSystem($taskStatus);

        $actor = User::findOrFail($deletedBy);

        return $this->deleteResource->forceHandle(
            $taskStatus,
            function (TaskStatus $taskStatus) use ($actor): void {
                $this->auditLogService->record(
                    Log::ACTION_FORCE_DELETE_TASK_STATUS,
                    $actor,
                    $taskStatus,
                    ['before' => $this->auditLogService->snapshot($taskStatus)],
                );
            });
    }

    /**
     * Delete multiple task statuses.
     *
     * System statuses are skipped rather than rejected, so one protected row
     * cannot roll back the whole batch. The returned count excludes them.
     *
     * @throws \Exception
     */
    public function deleteMultiple(
        array $taskStatusIds,
        int $deletedBy
    ): int {
        $count = 0;

        DB::transaction(function () use ($taskStatusIds, $deletedBy, &$count) {
            $taskStatuses = TaskStatus::whereIn('id', $taskStatusIds)->get();

            foreach ($taskStatuses as $taskStatus) {
                if ($taskStatus->isSystem()) {
                    continue;
                }

                if ($this->delete($taskStatus, $deletedBy)) {
                    $count++;
                }
            }
        });

        return $count;
    }

    /**
     * Refuse to delete a protected system status.
     *
     * @throws \DomainException
     */
    protected function ensureNotSystem(TaskStatus $taskStatus): void
    {
        if ($taskStatus->isSystem()) {
            throw new \DomainException('System statuses cannot be deleted.');
        }
    }
}
