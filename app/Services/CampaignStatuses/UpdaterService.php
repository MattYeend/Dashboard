<?php

declare(strict_types=1);

namespace App\Services\CampaignStatuses;

use App\Actions\UpdateResource;
use App\Models\CampaignStatus;
use App\Models\Log;
use App\Models\User;
use App\Services\AuditLogService;

class UpdaterService
{
    /**
     * Inject the required services into the updater service.
     */
    public function __construct(
        protected readonly DataPreparationService $dataPreparation,
        protected readonly AuditLogService $auditLogService,
        protected readonly UpdateResource $updateResource,
    ) {}

    /**
     * Update an existing campaign status.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws \DomainException When a system status would be renamed.
     * @throws \Exception
     */
    public function update(
        CampaignStatus $campaignStatuses,
        array $data,
        int $updatedBy
    ): CampaignStatus {
        $actor = User::findOrFail($updatedBy);

        $before = $this->auditLogService->snapshot($campaignStatuses);

        $dealStatusData = $this->dataPreparation->prepareForUpdate($data);

        $this->ensureNotRenamed($campaignStatuses, $dealStatusData);

        return $this->updateResource->handle(
            $campaignStatuses,
            $dealStatusData,
            function (CampaignStatus $campaignStatuses) use ($actor, $before, $updatedBy): void {
                $campaignStatuses->forceFill([
                    'updated_by' => $updatedBy,
                ])->save();
                $fresh = $campaignStatuses->fresh();

                $this->auditLogService->record(
                    Log::ACTION_UPDATE_CAMPAIGN_STATUS,
                    $actor,
                    $fresh,
                    [
                        'before' => $before,
                        'after' => $this->auditLogService->snapshot($fresh),
                    ],
                );
            });
    }

    /**
     * Refuse to rename a protected system status.
     *
     * The title is part of the workflow contract for system statuses. An
     * unchanged title is allowed so edit forms that always send it still work.
     *
     * @param  array<string, mixed>  $payload
     *
     * @throws \DomainException
     */
    protected function ensureNotRenamed(CampaignStatus $campaignStatuses, array $payload): void
    {
        if (
            $campaignStatuses->isSystem()
            && array_key_exists('title', $payload)
            && $payload['title'] !== $campaignStatuses->title
        ) {
            throw new \DomainException('System statuses cannot be renamed.');
        }
    }
}
