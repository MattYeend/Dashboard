<?php

declare(strict_types=1);

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
     * @throws \DomainException When the status is a protected system status.
     * @throws \Exception
     */
    public function delete(
        DealStatus $dealStatus,
        int $deletedBy,
        ?User $actor = null
    ): bool {
        $this->ensureNotSystem($dealStatus);

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
     * @throws \DomainException When the status is a protected system status.
     * @throws \Exception
     */
    public function forceDelete(
        DealStatus $dealStatus,
        int $deletedBy
    ): bool {
        $this->ensureNotSystem($dealStatus);

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
     * System statuses are skipped rather than rejected, so one protected row
     * cannot roll back the whole batch. The returned count excludes them.
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
                if ($dealStatus->isSystem()) {
                    continue;
                }

                if ($this->delete($dealStatus, $deletedBy)) {
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
    protected function ensureNotSystem(DealStatus $dealStatus): void
    {
        if ($dealStatus->isSystem()) {
            throw new \DomainException('System statuses cannot be deleted.');
        }
    }
}
