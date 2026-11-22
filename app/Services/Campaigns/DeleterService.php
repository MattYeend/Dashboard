<?php

declare(strict_types=1);

namespace App\Services\Campaigns;

use App\Actions\DeleteResource;
use App\Models\Campaign;
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
     * Soft delete a campaign.
     *
     * @throws AuthorizationException When the campaign is not in a deletable status.
     * @throws \Exception
     */
    public function delete(
        Campaign $campaign,
        int $deletedBy,
        ?User $actor = null
    ): bool {
        $this->ensureDeletable($campaign);

        $actor ??= User::findOrFail($deletedBy);

        return $this->deleteResource->handle(
            $campaign,
            function (Campaign $campaign) use ($actor, $deletedBy): void {
                $campaign->deleted_by = $deletedBy;
                $campaign->deleted_at = now();
                $campaign->save();

                $this->auditLogService->record(
                    Log::ACTION_DELETE_CAMPAIGN,
                    $actor,
                    $campaign,
                    ['before' => $this->auditLogService->snapshot($campaign)],
                );
            });
    }

    /**
     * Force delete a campaign (permanent deletion).
     *
     * @throws \Exception
     */
    public function forceDelete(
        Campaign $campaign,
        int $deletedBy
    ): bool {
        $actor = User::findOrFail($deletedBy);

        return $this->deleteResource->forceHandle(
            $campaign,
            function (Campaign $campaign) use ($actor): void {
                $this->auditLogService->record(
                    Log::ACTION_FORCE_DELETE_CAMPAIGN,
                    $actor,
                    $campaign,
                    ['before' => $this->auditLogService->snapshot($campaign)],
                );
            });
    }

    /**
     * Delete multiple campaigns.
     *
     * Campaigns that are not in a deletable status are skipped rather than
     * rejected, so one protected row cannot roll back the whole batch. The
     * returned count excludes them.
     *
     * @param  array<int, int>  $campaignIds
     *
     * @throws \Exception
     */
    public function deleteMultiple(
        array $campaignIds,
        int $deletedBy
    ): int {
        $count = 0;

        DB::transaction(function () use ($campaignIds, $deletedBy, &$count): void {
            $actor = User::findOrFail($deletedBy);

            $campaigns = Campaign::whereIn('id', $campaignIds)->get();

            foreach ($campaigns as $campaign) {
                if (! $this->activeChecker->canBeDeleted($campaign)) {
                    continue;
                }

                if ($this->delete($campaign, $deletedBy, $actor)) {
                    $count++;
                }
            }
        });

        return $count;
    }

    /**
     * Refuse to delete a campaign that is not in a deletable status.
     *
     * @throws AuthorizationException
     */
    protected function ensureDeletable(Campaign $campaign): void
    {
        if (! $this->activeChecker->canBeDeleted($campaign)) {
            throw new AuthorizationException(
                'Only campaigns in these statuses can be deleted: '
                .implode(', ', $this->activeChecker->deletableStatuses())
            );
        }
    }
}