<?php

declare(strict_types=1);

namespace App\Services\CampaignRecipients;

use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Models\User;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ManagementService
{
    /**
     * Inject the required services into the management service.
     */
    public function __construct(
        protected readonly CreatorService $creator,
        protected readonly UpdaterService $updater,
        protected readonly DeleterService $destructor,
        protected readonly RestorerService $restorer,
        protected readonly ExporterService $exporter,
    ) {}

    /**
     * Create a new campaign recipient for a campaign (pipeline use only).
     *
     * @param  array<string, mixed>  $data
     */
    public function store(
        Campaign $campaign,
        array $data,
        int $createdBy
    ): CampaignRecipient {
        return $this->creator->create(
            array_merge($data, ['campaign_id' => $campaign->id]),
            $createdBy
        );
    }

    /**
     * Update an existing campaign recipient (pipeline use only).
     *
     * @param  array<string, mixed>  $data
     */
    public function update(
        CampaignRecipient $campaignRecipient,
        array $data,
        int $updatedBy
    ): CampaignRecipient {
        return $this->updater->update($campaignRecipient, $data, $updatedBy);
    }

    /**
     * Soft delete a campaign recipient.
     */
    public function destroy(
        CampaignRecipient $campaignRecipient,
        User $actor
    ): void {
        $this->destructor->delete($campaignRecipient, $actor->id, $actor);
    }

    /**
     * Restore a soft-deleted campaign recipient of the given campaign.
     */
    public function restore(
        int $id,
        Campaign $campaign,
        User $actor
    ): CampaignRecipient {
        $campaignRecipient = CampaignRecipient::onlyTrashed()
            ->where('campaign_id', $campaign->id)
            ->findOrFail($id);

        return $this->restorer->restore($campaignRecipient, $actor->id, $actor);
    }

    /**
     * Force delete a campaign recipient of the given campaign, permanently
     * removing it from the database.
     */
    public function forceDelete(
        int $id,
        Campaign $campaign,
        User $actor
    ): void {
        $campaignRecipient = CampaignRecipient::onlyTrashed()
            ->where('campaign_id', $campaign->id)
            ->findOrFail($id);

        $this->destructor->forceDelete($campaignRecipient, $actor->id);
    }

    /**
     * Bulk restore campaign recipients of the given campaign.
     *
     * @param  array<int, int>  $ids
     * @return array{restored: array<int, int>, skipped: array<int, int>}
     */
    public function bulkRestore(
        array $ids,
        Campaign $campaign,
        User $actor,
        callable $authoriseCallback
    ): array {
        $requestedIds = collect($ids)->unique()->values();

        $campaignRecipients = CampaignRecipient::onlyTrashed()
            ->where('campaign_id', $campaign->id)
            ->whereIn('id', $requestedIds)
            ->get();

        $restored = [];

        foreach ($campaignRecipients as $campaignRecipient) {
            /** @var CampaignRecipient $campaignRecipient */
            $authoriseCallback($campaignRecipient);
            $this->restorer->restore($campaignRecipient, $actor->id, $actor);
            $restored[] = $campaignRecipient->id;
        }

        return [
            'restored' => $restored,
            'skipped' => $requestedIds
                ->diff($campaignRecipients->pluck('id'))
                ->values()
                ->all(),
        ];
    }

    /**
     * Bulk soft delete campaign recipients of the given campaign.
     *
     * The authorise callback runs the policy check before the deleter is reached.
     *
     * @param  array<int, int>  $ids
     * @return array{deleted: array<int, int>, skipped: array<int, int>}
     */
    public function bulkDelete(
        array $ids,
        Campaign $campaign,
        User $actor,
        callable $authoriseCallback
    ): array {
        $requestedIds = collect($ids)->unique()->values();

        $campaignRecipients = CampaignRecipient::where('campaign_id', $campaign->id)
            ->whereIn('id', $requestedIds)
            ->get();

        $deleted = [];

        foreach ($campaignRecipients as $campaignRecipient) {
            /** @var CampaignRecipient $campaignRecipient */
            $authoriseCallback($campaignRecipient);
            $this->destructor->delete($campaignRecipient, $actor->id, $actor);
            $deleted[] = $campaignRecipient->id;
        }

        return [
            'deleted' => $deleted,
            'skipped' => $requestedIds
                ->diff($campaignRecipients->pluck('id'))
                ->values()
                ->all(),
        ];
    }

    /**
     * Export campaign recipients of a campaign matching the given filters
     * as a CSV download.
     *
     * @param  array<string, mixed>  $filters
     */
    public function export(Campaign $campaign, array $filters): StreamedResponse
    {
        return $this->exporter->export($campaign, $filters);
    }
}