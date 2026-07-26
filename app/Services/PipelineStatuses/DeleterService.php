<?php

namespace App\Services\PipelineStatuses;

use App\Actions\DeleteResource;
use App\Models\Log;
use App\Models\PipelineStatus;
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
     * Soft delete a pipelineStatus.
     *
     * @throws \Exception
     */
    public function delete(
        PipelineStatus $pipelineStatus,
        int $deletedBy,
        ?User $actor = null
    ): bool {

        $actor ??= User::findOrFail($deletedBy);

        return $this->deleteResource->handle(
            $pipelineStatus,
            function (PipelineStatus $pipelineStatus) use ($actor, $deletedBy): void {
                $pipelineStatus->deleted_by = $deletedBy;
                $pipelineStatus->deleted_at = now();
                $pipelineStatus->save();

                $this->auditLogService->record(
                    Log::ACTION_DELETE_PIPELINE_STATUS,
                    $actor,
                    $pipelineStatus,
                    ['before' => $this->auditLogService->snapshot($pipelineStatus)],
                );
            });
    }

    /**
     * Force delete a pipelineStatus (permanent deletion).
     *
     * @throws \Exception
     */
    public function forceDelete(
        PipelineStatus $pipelineStatus,
        int $deletedBy
    ): bool {
        $actor = User::findOrFail($deletedBy);

        return $this->deleteResource->forceHandle(
            $pipelineStatus,
            function (PipelineStatus $pipelineStatus) use ($actor): void {
                $this->auditLogService->record(
                    Log::ACTION_FORCE_DELETE_PIPELINE_STATUS,
                    $actor,
                    $pipelineStatus,
                    ['before' => $this->auditLogService->snapshot($pipelineStatus)],
                );
            });
    }

    /**
     * Delete multiple pipelineStatuses.
     *
     * @throws \Exception
     */
    public function deleteMultiple(
        array $pipelineStatusIds,
        int $deletedBy
    ): int {
        $count = 0;

        DB::transaction(function () use ($pipelineStatusIds, $deletedBy, &$count) {
            $pipelineStatuses = PipelineStatus::whereIn('id', $pipelineStatusIds)->get();

            foreach ($pipelineStatuses as $pipelineStatus) {
                if ($this->delete($pipelineStatus, $deletedBy)) {
                    $count++;
                }
            }
        });

        return $count;
    }
}