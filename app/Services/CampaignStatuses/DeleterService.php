<?php

declare(strict_types=1);

namespace App\Services\CampaignStatuses;

use App\Actions\DeleteResource;
use App\Models\CampaignStatus;
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
     * Soft delete a campaign status.
     *
     * @throws \DomainException When the status is a protected system status.
     * @throws \Exception
     */
    public function delete(
        CampaignStatus $campaignStatus,
        int $deletedBy,
        ?User $actor = null
    ): bool {
        $this->ensureNotSystem($campaignStatus);

        $actor ??= User::findOrFail($deletedBy);

        return $this->deleteResource->handle(
            $campaignStatus,
            function (CampaignStatus $campaignStatus) use ($actor, $deletedBy): void {
                $campaignStatus->deleted_by = $deletedBy;
                $campaignStatus->deleted_at = now();
                $campaignStatus->save();

                $this->auditLogService->record(
                    Log::ACTION_DELETE_CAMPAIGN_STATUS,
                    $actor,
                    $campaignStatus,
                    ['before' => $this->auditLogService->snapshot($campaignStatus)],
                );
            });
    }

    /**
     * Force delete a campaign status (permanent deletion).
     *
     * @throws \DomainException When the status is a protected system status.
     * @throws \Exception
     */
    public function forceDelete(
        CampaignStatus $campaignStatus,
        int $deletedBy
    ): bool {
        $this->ensureNotSystem($campaignStatus);

        $actor = User::findOrFail($deletedBy);

        return $this->deleteResource->forceHandle(
            $campaignStatus,
            function (CampaignStatus $campaignStatus) use ($actor): void {
                $this->auditLogService->record(
                    Log::ACTION_FORCE_DELETE_CAMPAIGN_STATUS,
                    $actor,
                    $campaignStatus,
                    ['before' => $this->auditLogService->snapshot($campaignStatus)],
                );
            });
    }

    /**
     * Delete multiple campaign statuses.
     *
     * System statuses are skipped rather than rejected, so one protected row
     * cannot roll back the whole batch. The returned count excludes them.
     *
     * @throws \Exception
     */
    public function deleteMultiple(
        array $campaignStatusIds,
        int $deletedBy
    ): int {
        $count = 0;

        DB::transaction(function () use ($campaignStatusIds, $deletedBy, &$count) {
            $campaignStatuses = CampaignStatus::whereIn('id', $campaignStatusIds)->get();

            foreach ($campaignStatuses as $campaignStatus) {
                if ($campaignStatus->isSystem()) {
                    continue;
                }

                if ($this->delete($campaignStatus, $deletedBy)) {
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
    protected function ensureNotSystem(CampaignStatus $campaignStatus): void
    {
        if ($campaignStatus->isSystem()) {
            throw new \DomainException('System statuses cannot be deleted.');
        }
    }
}
