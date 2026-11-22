<?php

declare(strict_types=1);

namespace App\Services\Campaigns;

use App\Actions\CreateResource;
use App\Models\Campaign;
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
     * Create a new campaign.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ModelNotFoundException
     */
    public function create(array $data, int $createdBy): Campaign
    {
        $actor = User::findOrFail($createdBy);

        return $this->createResource->handle(
            $data,
            function (array $data) use ($createdBy, $actor): Campaign {
                $campaignData = $this->dataPreparation->prepareForCreation($data);

                $newCampaign = Campaign::create($campaignData);

                $newCampaign->forceFill([
                    'created_by' => $createdBy,
                ])->save();

                $this->auditLogService->record(
                    Log::ACTION_CREATE_CAMPAIGN,
                    $actor,
                    $newCampaign,
                    ['after' => $this->auditLogService->snapshot($newCampaign)],
                );

                return $newCampaign;
            });
    }
}