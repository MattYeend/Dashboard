<?php

declare(strict_types=1);

namespace App\Services\CampaignRecipients;

use App\Actions\UpdateResource;
use App\Models\CampaignRecipient;
use App\Models\Log;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Auth\Access\AuthorizationException;

class UpdaterService
{
    /**
     * Inject the required services into the updater service.
     */
    public function __construct(
        protected readonly DataPreparationService $dataPreparation,
        protected readonly ActiveCheckerService $activeChecker,
        protected readonly AuditLogService $auditLogService,
        protected readonly UpdateResource $updateResource,
    ) {}

    /**
     * Update an existing campaign recipient.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws AuthorizationException When the campaign recipient is deleted.
     * @throws \Exception
     */
    public function update(
        CampaignRecipient $campaignRecipient,
        array $data,
        int $updatedBy
    ): CampaignRecipient {
        $this->ensureEditable($campaignRecipient);

        $actor = User::findOrFail($updatedBy);

        $before = $this->auditLogService->snapshot($campaignRecipient);

        $campaignRecipientData = $this->dataPreparation->prepareForUpdate($data);

        return $this->updateResource->handle(
            $campaignRecipient,
            $campaignRecipientData,
            function (CampaignRecipient $campaignRecipient) use ($actor, $before, $updatedBy): void {
                $campaignRecipient->forceFill([
                    'updated_by' => $updatedBy,
                ])->save();
                $fresh = $campaignRecipient->fresh();

                $this->auditLogService->record(
                    Log::ACTION_UPDATE_CAMPAIGN_RECIPIENT,
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
     * Refuse to edit a campaign recipient that has been deleted.
     *
     * @throws AuthorizationException
     */
    protected function ensureEditable(CampaignRecipient $campaignRecipient): void
    {
        if (! $this->activeChecker->isEditable($campaignRecipient)) {
            throw new AuthorizationException('A deleted campaign recipient cannot be edited.');
        }
    }
}