<?php

declare(strict_types=1);

namespace App\Services\CampaignStatuses;

use App\Actions\CreateResource;
use App\Models\CampaignStatus;
use App\Models\Log;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CreatorService
{
    /**
     * Inject the required services into the creator service.
     */
    public function __construct(
        protected readonly DataPreparationService $dataPreparation,
        protected readonly AuditLogService $auditLogService,
        protected readonly CreateResource $createResource,
    ) {}

    /**
     * Create a new campaign status.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ModelNotFoundException
     */
    public function create(array $data, int $createdBy): CampaignStatus
    {
        $actor = User::findOrFail($createdBy);

        return $this->createResource->handle(
            $data,
            function (array $data) use ($createdBy, $actor): CampaignStatus {
                $dealStatusData = $this->dataPreparation->prepareForCreation($data);

                $newDealStatus = CampaignStatus::create($dealStatusData);

                $newDealStatus->forceFill([
                    'created_by' => $createdBy,
                ])->save();

                $this->auditLogService->record(
                    Log::ACTION_CREATE_CAMPAIGN_STATUS,
                    $actor,
                    $newDealStatus,
                    ['after' => $this->auditLogService->snapshot($newDealStatus)],
                );

                return $newDealStatus;
            });
    }
}
