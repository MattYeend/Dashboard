<?php

declare(strict_types=1);

namespace App\Services\Campaigns;

use App\Actions\UpdateResource;
use App\Models\Campaign;
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
     * Update an existing campaign.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws AuthorizationException When the campaign is not in an editable status.
     * @throws \Exception
     */
    public function update(
        Campaign $campaign,
        array $data,
        int $updatedBy
    ): Campaign {
        $this->ensureEditable($campaign);

        $actor = User::findOrFail($updatedBy);

        $before = $this->auditLogService->snapshot($campaign);

        $campaignData = $this->dataPreparation->prepareForUpdate($data);

        return $this->updateResource->handle(
            $campaign,
            $campaignData,
            function (Campaign $campaign) use ($actor, $before, $updatedBy): void {
                $campaign->forceFill([
                    'updated_by' => $updatedBy,
                ])->save();
                $fresh = $campaign->fresh();

                $this->auditLogService->record(
                    Log::ACTION_UPDATE_CAMPAIGN,
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
     * Refuse to edit a campaign that is not in an editable status.
     *
     * @throws AuthorizationException
     */
    protected function ensureEditable(Campaign $campaign): void
    {
        if (! $this->activeChecker->isEditable($campaign)) {
            throw new AuthorizationException(
                'Only campaigns in these statuses can be edited: '
                .implode(', ', $this->activeChecker->editableStatuses())
            );
        }
    }
}
