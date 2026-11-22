<?php

declare(strict_types=1);

namespace App\Services\CampaignSegments;

use App\Actions\UpdateResource;
use App\Models\CampaignSegment;
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
     * Update an existing campaign segment.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws \Exception
     */
    public function update(
        CampaignSegment $campaignSegment,
        array $data,
        int $updatedBy
    ): CampaignSegment {
        $actor = User::findOrFail($updatedBy);

        $before = $this->auditLogService->snapshot($campaignSegment);

        $payload = $this->dataPreparation->prepareForUpdate($data);

        return $this->updateResource->handle(
            $campaignSegment,
            $payload,
            function (CampaignSegment $campaignSegment) use ($actor, $before, $updatedBy): void {
                $campaignSegment->forceFill([
                    'updated_by' => $updatedBy,
                ])->save();

                $fresh = $campaignSegment->fresh();

                $this->auditLogService->record(
                    Log::ACTION_UPDATE_CAMPAIGN_SEGMENT,
                    $actor,
                    $fresh,
                    [
                        'before' => $before,
                        'after' => $this->auditLogService->snapshot($fresh),
                    ],
                );
            });
    }
}