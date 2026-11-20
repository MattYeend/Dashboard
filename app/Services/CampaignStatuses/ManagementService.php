<?php

declare(strict_types=1);

namespace App\Services\CampaignStatuses;

use App\Http\Requests\CampaignStatuses\ImportCampaignStatusRequest;
use App\Http\Requests\CampaignStatuses\StoreCampaignStatusRequest;
use App\Http\Requests\CampaignStatuses\UpdateCampaignStatusRequest;
use App\Models\CampaignStatus;
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
     * Create a new campaign status.
     */
    public function store(
        StoreCampaignStatusRequest $request
    ): CampaignStatus {
        return $this->creator->create(
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Update an existing campaign status.
     */
    public function update(
        UpdateCampaignStatusRequest $request,
        CampaignStatus $campaignStatus
    ): CampaignStatus {
        return $this->updater->update(
            $campaignStatus,
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Soft delete a campaign status.
     */
    public function destroy(
        CampaignStatus $campaignStatus,
        User $actor
    ): void {
        $this->destructor->delete($campaignStatus, $actor->id);
    }

    /**
     * Restore a soft-deleted campaign status.
     */
    public function restore(
        int $id,
        User $actor
    ): CampaignStatus {
        $campaignStatus = CampaignStatus::withTrashed()->findOrFail($id);

        return $this->restorer->restore($campaignStatus, $actor->id);
    }

    /**
     * Force delete a campaign status, permanently removing it from the
     * database.
     */
    public function forceDelete(
        int $id,
        User $actor
    ): void {
        $campaignStatus = CampaignStatus::withTrashed()->findOrFail($id);
        $this->destructor->forceDelete($campaignStatus, $actor->id);
    }

    /**
     * Bulk restore campaign statuses.
     */
    public function bulkRestore(
        array $ids,
        User $actor,
        callable $authoriseCallback
    ): array {
        $requestedIds = collect($ids)->unique()->values();

        $campaignStatuses = CampaignStatus::onlyTrashed()
            ->whereIn('id', $requestedIds)
            ->get();

        $restored = [];

        foreach ($campaignStatuses as $campaignStatus) {
            /** @var CampaignStatus $campaignStatus */
            $authoriseCallback($campaignStatus);
            $this->restorer->restore($campaignStatus, $actor->id);
            $restored[] = $campaignStatus->id;
        }

        return [
            'restored' => $restored,
            'skipped' => $requestedIds
                ->diff($campaignStatuses->pluck('id'))
                ->values()
                ->all(),
        ];
    }

    /**
     * Bulk soft delete campaign statuses.
     */
    public function bulkDelete(
        array $ids,
        User $actor,
        callable $authoriseCallback
    ): array {
        $requestedIds = collect($ids)->unique()->values();

        $campaignStatuses = CampaignStatus::whereIn('id', $requestedIds)
            ->get();

        $deleted = [];

        foreach ($campaignStatuses as $campaignStatus) {
            /** @var CampaignStatus $campaignStatus */
            $authoriseCallback($campaignStatus);
            $this->destructor->delete($campaignStatus, $actor->id);
            $deleted[] = $campaignStatus->id;
        }

        return [
            'deleted' => $deleted,
            'skipped' => $requestedIds
                ->diff($campaignStatuses->pluck('id'))
                ->values()
                ->all(),
        ];
    }

    /**
     * Import campaign statuses from an uploaded file.
     *
     * @return array{imported: int, skipped: array<int, array{row: int, reason: string}>}
     */
    public function import(
        ImportCampaignStatusRequest $request
    ): array {
        return $this->importer->import(
            $request->file('file'),
            $request->user()->id
        );
    }

    /**
     * Export campaign statuses matching the given filters as a CSV download.
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
