<?php

namespace App\Services\DealStatuses;

use App\Http\Requests\DealStatuses\ImportDealStatusRequest;
use App\Http\Requests\DealStatuses\StoreDealStatusRequest;
use App\Http\Requests\DealStatuses\UpdateDealStatusRequest;
use App\Models\DealStatus;
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
     * Create a new deal status.
     */
    public function store(
        StoreDealStatusRequest $request
    ): DealStatus {
        return $this->creator->create(
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Update an existing deal status.
     */
    public function update(
        UpdateDealStatusRequest $request,
        DealStatus $dealStatus
    ): DealStatus {
        return $this->updater->update(
            $dealStatus,
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Soft delete a deal status.
     */
    public function destroy(
        DealStatus $dealStatus,
        User $actor
    ): void {
        $this->destructor->delete($dealStatus, $actor->id);
    }

    /**
     * Restore a soft-deleted deal status.
     */
    public function restore(
        int $id,
        User $actor
    ): DealStatus {
        $dealStatus = DealStatus::withTrashed()->findOrFail($id);

        return $this->restorer->restore($dealStatus, $actor->id);
    }

    /**
     * Force delete a deal status, permanently removing it from the
     * database.
     */
    public function forceDelete(
        int $id,
        User $actor
    ): void {
        $dealStatus = DealStatus::withTrashed()->findOrFail($id);
        $this->destructor->forceDelete($dealStatus, $actor->id);
    }

    /**
     * Bulk restore deal statuses.
     */
    public function bulkRestore(
        array $ids,
        User $actor,
        callable $authoriseCallback
    ): array {
        $requestedIds = collect($ids)->unique()->values();

        $dealStatuses = DealStatus::onlyTrashed()
            ->whereIn('id', $requestedIds)
            ->get();

        $restored = [];

        foreach ($dealStatuses as $dealStatus) {
            /** @var DealStatus $dealStatus */
            $authoriseCallback($dealStatus);
            $this->restorer->restore($dealStatus, $actor->id);
            $restored[] = $dealStatus->id;
        }

        return [
            'restored' => $restored,
            'skipped' => $requestedIds
                ->diff($dealStatuses->pluck('id'))
                ->values()
                ->all(),
        ];
    }

    /**
     * Bulk soft delete deal statuses.
     */
    public function bulkDelete(
        array $ids,
        User $actor,
        callable $authoriseCallback
    ): array {
        $requestedIds = collect($ids)->unique()->values();

        $dealStatuses = DealStatus::whereIn('id', $requestedIds)
            ->get();

        $deleted = [];

        foreach ($dealStatuses as $dealStatus) {
            /** @var DealStatus $dealStatus */
            $authoriseCallback($dealStatus);
            $this->destructor->delete($dealStatus, $actor->id);
            $deleted[] = $dealStatus->id;
        }

        return [
            'deleted' => $deleted,
            'skipped' => $requestedIds
                ->diff($dealStatuses->pluck('id'))
                ->values()
                ->all(),
        ];
    }

    /**
     * Import deal statuses from an uploaded file.
     *
     * @return array{imported: int, skipped: array<int, array{row: int, reason: string}>}
     */
    public function import(
        ImportDealStatusRequest $request
    ): array {
        return $this->importer->import(
            $request->file('file'),
            $request->user()->id
        );
    }

    /**
     * Export deal statuses matching the given filters as a CSV download.
     *
     * @param  array<string, mixed>  $filters
     */
    public function export(array $filters): StreamedResponse
    {
        return $this->exporter->export($filters);
    }
}
