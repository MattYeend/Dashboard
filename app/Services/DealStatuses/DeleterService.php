<?php

namespace App\Services\DealStatuses;

use App\Actions\DeleteResource;
use App\Models\DealStatus;
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
     * Soft delete a deal status.
     *
     * @throws \Exception
     */
    public function delete(
        DealStatus $dealStatus,
        int $deletedBy,
        ?User $actor = null
    ): bool {

        $actor ??= User::findOrFail($deletedBy);

        return $this->deleteResource->handle(
            $dealStatus,
            function (DealStatus $dealStatus) use ($actor, $deletedBy): void {
                $dealStatus->deleted_by = $deletedBy;
                $dealStatus->deleted_at = now();
                $dealStatus->save();

                $this->auditLogService->record(
                    Log::ACTION_DELETE_DEAL_STATUS,
                    $actor,
                    $dealStatus,
                    ['before' => $this->auditLogService->snapshot($dealStatus)],
                );
            });
    }

    /**
     * Force delete a deal status (permanent deletion).
     *
     * @throws \Exception
     */
    public function forceDelete(
        DealStatus $dealStatus,
        int $deletedBy
    ): bool {
        $actor = User::findOrFail($deletedBy);

        return $this->deleteResource->forceHandle(
            $dealStatus,
            function (DealStatus $dealStatus) use ($actor): void {
                $this->auditLogService->record(
                    Log::ACTION_FORCE_DELETE_DEAL_STATUS,
                    $actor,
                    $dealStatus,
                    ['before' => $this->auditLogService->snapshot($dealStatus)],
                );
            });
    }

    /**
     * Delete multiple deal statuses.
     *
     * @throws \Exception
     */
    public function deleteMultiple(
        array $dealStatusIds,
        int $deletedBy
    ): int {
        $count = 0;

        DB::transaction(function () use ($dealStatusIds, $deletedBy, &$count) {
            $dealStatuses = DealStatus::whereIn('id', $dealStatusIds)->get();

            foreach ($dealStatuses as $dealStatus) {
                if ($this->delete($dealStatus, $deletedBy)) {
                    $count++;
                }
            }
        });

        return $count;
    }
}
