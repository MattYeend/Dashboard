<?php

declare(strict_types=1);

namespace App\Services\CampaignRecipients;

use App\Actions\CreateResource;
use App\Models\CampaignRecipient;
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
     * Create a new campaign recipient.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ModelNotFoundException
     */
    public function create(array $data, int $createdBy): CampaignRecipient
    {
        $actor = User::findOrFail($createdBy);

        return $this->createResource->handle(
            $data,
            function (array $data) use ($createdBy, $actor): CampaignRecipient {
                $campaignRecipientData = $this->dataPreparation->prepareForCreation($data);

                $newCampaignRecipient = CampaignRecipient::create($campaignRecipientData);

                $newCampaignRecipient->forceFill([
                    'created_by' => $createdBy,
                ])->save();

                $this->auditLogService->record(
                    Log::ACTION_CREATE_CAMPAIGN_RECIPIENT,
                    $actor,
                    $newCampaignRecipient,
                    ['after' => $this->auditLogService->snapshot($newCampaignRecipient)],
                );

                return $newCampaignRecipient;
            });
    }
}
