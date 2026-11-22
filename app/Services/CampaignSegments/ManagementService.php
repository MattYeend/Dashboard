<?php

declare(strict_types=1);

namespace App\Services\CampaignSegments;

use App\Http\Requests\CampaignSegments\ImportCampaignSegmentRequest;
use App\Http\Requests\CampaignSegments\StoreCampaignSegmentRequest;
use App\Http\Requests\CampaignSegments\UpdateCampaignSegmentRequest;
use App\Models\CampaignSegment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
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
        protected readonly ActiveCheckerService $activeChecker,
    ) {}

    /**
     * Create a new campaign segment.
     */
    public function store(StoreCampaignSegmentRequest $request): CampaignSegment
    {
        return $this->creator->create(
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Update an existing campaign segment.
     */
    public function update(UpdateCampaignSegmentRequest $request, CampaignSegment $campaignSegment): CampaignSegment
    {
        return $this->updater->update(
            $campaignSegment,
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Soft delete a campaign segment.
     */
    public function destroy(CampaignSegment $campaignSegment, User $actor): void
    {
        $this->destructor->delete($campaignSegment, $actor->id, $actor);
    }

    /**
     * Restore a soft-deleted campaign segment.
     */
    public function restore(int $id, User $actor): CampaignSegment
    {
        $campaignSegment = CampaignSegment::onlyTrashed()->findOrFail($id);

        return $this->restorer->restore($campaignSegment, $actor->id, $actor);
    }

    /**
     * Force delete a campaign segment, permanently removing it from the database.
     */
    public function forceDelete(int $id, User $actor): void
    {
        $campaignSegment = CampaignSegment::onlyTrashed()->findOrFail($id);

        $this->destructor->forceDelete($campaignSegment, $actor->id);
    }

    /**
     * Bulk restore campaign segments.
     *
     * @param  array<int, int>  $ids
     * @return array{restored: array<int, int>, skipped: array<int, int>}
     */
    public function bulkRestore(array $ids, User $actor, callable $authoriseCallback): array
    {
        $requestedIds = collect($ids)->unique()->values();

        return DB::transaction(function () use ($requestedIds, $actor, $authoriseCallback): array {
            $campaignSegments = CampaignSegment::onlyTrashed()
                ->whereIn('id', $requestedIds)
                ->orderBy('id')
                ->get();

            $restored = [];

            foreach ($campaignSegments as $campaignSegment) {
                /** @var CampaignSegment $campaignSegment */
                $authoriseCallback($campaignSegment);
                $this->restorer->restore($campaignSegment, $actor->id, $actor);
                $restored[] = $campaignSegment->id;
            }

            return [
                'restored' => $restored,
                'skipped' => $requestedIds
                    ->diff($campaignSegments->pluck('id'))
                    ->values()
                    ->all(),
            ];
        });
    }

    /**
     * Bulk soft delete campaign segments.
     *
     * Segments locked by a scheduled or sending campaign are skipped.
     *
     * @param  array<int, int>  $ids
     * @return array{deleted: array<int, int>, skipped: array<int, int>}
     */
    public function bulkDelete(array $ids, User $actor, callable $authoriseCallback): array
    {
        $requestedIds = collect($ids)->unique()->values();

        return DB::transaction(function () use ($requestedIds, $actor, $authoriseCallback): array {
            $campaignSegments = CampaignSegment::whereIn('id', $requestedIds)
                ->orderBy('id')
                ->get();

            $deleted = [];
            $locked = [];

            foreach ($campaignSegments as $campaignSegment) {
                /** @var CampaignSegment $campaignSegment */
                $authoriseCallback($campaignSegment);

                if (! $this->activeChecker->canBeDeleted($campaignSegment)) {
                    $locked[] = $campaignSegment->id;

                    continue;
                }

                $this->destructor->delete($campaignSegment, $actor->id, $actor);
                $deleted[] = $campaignSegment->id;
            }

            return [
                'deleted' => $deleted,
                'skipped' => $requestedIds
                    ->diff($campaignSegments->pluck('id'))
                    ->merge($locked)
                    ->values()
                    ->all(),
            ];
        });
    }

    /**
     * Import campaign segments from an uploaded file.
     *
     * @return array{imported: int, skipped: array<int, array{row: int, reason: string}>}
     */
    public function import(ImportCampaignSegmentRequest $request): array
    {
        return $this->importer->import(
            $request->file('file'),
            $request->user()->id
        );
    }

    /**
     * Export campaign segments matching the given filters as a CSV download.
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