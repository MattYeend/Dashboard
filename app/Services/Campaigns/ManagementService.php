<?php

declare(strict_types=1);

namespace App\Services\Campaigns;

use App\Http\Requests\Campaigns\ImportCampaignRequest;
use App\Http\Requests\Campaigns\StoreCampaignRequest;
use App\Http\Requests\Campaigns\UpdateCampaignRequest;
use App\Models\Campaign;
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
        protected readonly ImporterService $importer,
        protected readonly ExporterService $exporter,
    ) {}

    /**
     * Create a new campaign.
     */
    public function store(
        StoreCampaignRequest $request
    ): Campaign {
        return $this->creator->create(
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Update an existing campaign.
     */
    public function update(
        UpdateCampaignRequest $request,
        Campaign $campaign
    ): Campaign {
        return $this->updater->update(
            $campaign,
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Soft delete a campaign.
     */
    public function destroy(
        Campaign $campaign,
        User $actor
    ): void {
        $this->destructor->delete($campaign, $actor->id, $actor);
    }

    /**
     * Restore a soft-deleted campaign.
     */
    public function restore(
        int $id,
        User $actor
    ): Campaign {
        $campaign = Campaign::withTrashed()->findOrFail($id);

        return $this->restorer->restore($campaign, $actor->id, $actor);
    }

    /**
     * Force delete a campaign, permanently removing it from the database.
     */
    public function forceDelete(
        int $id,
        User $actor
    ): void {
        $campaign = Campaign::withTrashed()->findOrFail($id);

        $this->destructor->forceDelete($campaign, $actor->id);
    }

    /**
     * Bulk restore campaigns.
     *
     * @param  array<int, int>  $ids
     * @return array{restored: array<int, int>, skipped: array<int, int>}
     */
    public function bulkRestore(
        array $ids,
        User $actor,
        callable $authoriseCallback
    ): array {
        $requestedIds = collect($ids)->unique()->values();

        $campaigns = Campaign::onlyTrashed()
            ->whereIn('id', $requestedIds)
            ->get();

        $restored = [];

        foreach ($campaigns as $campaign) {
            /** @var Campaign $campaign */
            $authoriseCallback($campaign);
            $this->restorer->restore($campaign, $actor->id, $actor);
            $restored[] = $campaign->id;
        }

        return [
            'restored' => $restored,
            'skipped' => $requestedIds
                ->diff($campaigns->pluck('id'))
                ->values()
                ->all(),
        ];
    }

    /**
     * Bulk soft delete campaigns.
     *
     * The authorise callback runs the policy check, which rejects campaigns
     * that are not in a deletable status before the deleter is reached.
     *
     * @param  array<int, int>  $ids
     * @return array{deleted: array<int, int>, skipped: array<int, int>}
     */
    public function bulkDelete(
        array $ids,
        User $actor,
        callable $authoriseCallback
    ): array {
        $requestedIds = collect($ids)->unique()->values();

        $campaigns = Campaign::whereIn('id', $requestedIds)->get();

        $deleted = [];

        foreach ($campaigns as $campaign) {
            /** @var Campaign $campaign */
            $authoriseCallback($campaign);
            $this->destructor->delete($campaign, $actor->id, $actor);
            $deleted[] = $campaign->id;
        }

        return [
            'deleted' => $deleted,
            'skipped' => $requestedIds
                ->diff($campaigns->pluck('id'))
                ->values()
                ->all(),
        ];
    }

    /**
     * Import campaigns from an uploaded file.
     *
     * @return array{imported: int, skipped: array<int, array{row: int, reason: string}>}
     */
    public function import(
        ImportCampaignRequest $request
    ): array {
        return $this->importer->import(
            $request->file('file'),
            $request->user()->id
        );
    }

    /**
     * Export campaigns matching the given filters as a CSV download.
     *
     * @param  array<string, mixed>  $filters
     */
    public function export(array $filters): StreamedResponse
    {
        return $this->exporter->export($filters);
    }

    /**
     * Expose the importer service for controller-level import actions
     * (preview/commit) that operate outside the request-driven import().
     */
    public function importer(): ImporterService
    {
        return $this->importer;
    }
}
