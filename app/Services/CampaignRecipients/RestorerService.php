<?php

declare(strict_types=1);

namespace App\Services\CampaignRecipients;

use App\Actions\RestoreResource;
use App\Models\CampaignRecipient;
use App\Models\Log;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class RestorerService
{
    /**
     * Inject the required services into the restorer service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
        protected readonly RestoreResource $restoreResource,
    ) {}

    /**
     * Restore a soft-deleted campaign recipient.
     *
     * @throws \Exception
     */
    public function restore(
        CampaignRecipient $campaignRecipient,
        int $restoredBy,
        ?User $actor = null,
    ): CampaignRecipient {
        $actor ??= User::findOrFail($restoredBy);

        return $this->restoreResource->handle(
            $campaignRecipient,
            function (CampaignRecipient $campaignRecipient) use ($actor, $restoredBy): void {
                $campaignRecipient->restored_by = $restoredBy;
                $campaignRecipient->restored_at = now();
                $campaignRecipient->save();

                $this->auditLogService->record(
                    Log::ACTION_RESTORE_CAMPAIGN_RECIPIENT,
                    $actor,
                    $campaignRecipient,
                    ['before' => $this->auditLogService->snapshot($campaignRecipient)],
                );
            });
    }

    /**
     * Restore multiple soft-deleted campaign recipients.
     *
     * @param  array<int, int>  $campaignRecipientIds
     * @return int Number of campaign recipients restored
     *
     * @throws \Exception
     */
    public function restoreMultiple(
        array $campaignRecipientIds,
        int $restoredBy
    ): int {
        $count = 0;

        DB::transaction(function () use ($campaignRecipientIds, $restoredBy, &$count): void {
            $actor = User::findOrFail($restoredBy);

            /** @var Collection<int, CampaignRecipient> $campaignRecipients */
            $campaignRecipients = CampaignRecipient::onlyTrashed()
                ->whereIn('id', $campaignRecipientIds)
                ->get();

            foreach ($campaignRecipients as $campaignRecipient) {
                $this->restore($campaignRecipient, $restoredBy, $actor);
                $count++;
            }
        });

        return $count;
    }
}
