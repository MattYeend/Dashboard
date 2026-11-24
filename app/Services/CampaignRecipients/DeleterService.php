<?php

declare(strict_types=1);

namespace App\Services\CampaignRecipients;

use App\Actions\DeleteResource;
use App\Models\CampaignRecipient;
use App\Models\Log;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class DeleterService
{
    /**
     * Inject the required services into the deleter service.
     */
    public function __construct(
        protected readonly ActiveCheckerService $activeChecker,
        protected readonly AuditLogService $auditLogService,
        protected readonly DeleteResource $deleteResource,
    ) {}

    /**
     * Soft delete a campaign recipient.
     *
     * @throws AuthorizationException When the campaign recipient is already deleted.
     * @throws \Exception
     */
    public function delete(
        CampaignRecipient $campaignRecipient,
        int $deletedBy,
        ?User $actor = null
    ): bool {
        $this->ensureDeletable($campaignRecipient);

        $actor ??= User::findOrFail($deletedBy);

        return $this->deleteResource->handle(
            $campaignRecipient,
            function (CampaignRecipient $campaignRecipient) use ($actor, $deletedBy): void {
                $campaignRecipient->deleted_by = $deletedBy;
                $campaignRecipient->deleted_at = now();
                $campaignRecipient->save();

                $this->auditLogService->record(
                    Log::ACTION_DELETE_CAMPAIGN_RECIPIENT,
                    $actor,
                    $campaignRecipient,
                    ['before' => $this->auditLogService->snapshot($campaignRecipient)],
                );
            });
    }

    /**
     * Force delete a campaign recipient (permanent deletion).
     *
     * @throws \Exception
     */
    public function forceDelete(
        CampaignRecipient $campaignRecipient,
        int $deletedBy
    ): bool {
        $actor = User::findOrFail($deletedBy);

        return $this->deleteResource->forceHandle(
            $campaignRecipient,
            function (CampaignRecipient $campaignRecipient) use ($actor): void {
                $this->auditLogService->record(
                    Log::ACTION_FORCE_DELETE_CAMPAIGN_RECIPIENT,
                    $actor,
                    $campaignRecipient,
                    ['before' => $this->auditLogService->snapshot($campaignRecipient)],
                );
            });
    }

    /**
     * Delete multiple campaign recipients.
     *
     * Recipients that cannot be deleted are skipped rather than rejected, so
     * one protected row cannot roll back the whole batch. The returned count
     * excludes them.
     *
     * @param  array<int, int>  $campaignRecipientIds
     *
     * @throws \Exception
     */
    public function deleteMultiple(
        array $campaignRecipientIds,
        int $deletedBy
    ): int {
        $count = 0;

        DB::transaction(function () use ($campaignRecipientIds, $deletedBy, &$count): void {
            $actor = User::findOrFail($deletedBy);

            $campaignRecipients = CampaignRecipient::whereIn('id', $campaignRecipientIds)->get();

            foreach ($campaignRecipients as $campaignRecipient) {
                if (! $this->activeChecker->canBeDeleted($campaignRecipient)) {
                    continue;
                }

                if ($this->delete($campaignRecipient, $deletedBy, $actor)) {
                    $count++;
                }
            }
        });

        return $count;
    }

    /**
     * Refuse to delete a campaign recipient that is already deleted.
     *
     * @throws AuthorizationException
     */
    protected function ensureDeletable(CampaignRecipient $campaignRecipient): void
    {
        if (! $this->activeChecker->canBeDeleted($campaignRecipient)) {
            throw new AuthorizationException('This campaign recipient has already been deleted.');
        }
    }
}