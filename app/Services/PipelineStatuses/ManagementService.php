<?php

namespace App\Services\PipelineStatuses;

use App\Http\Requests\PipelineStatuses\ImportPipelineStatusRequest;
use App\Http\Requests\PipelineStatuses\StorePipelineStatusRequest;
use App\Http\Requests\PipelineStatuses\UpdatePipelineStatusRequest;
use App\Models\PipelineStatus;
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
     * Create a new pipeline status.
     */
    public function store(
        StorePipelineStatusRequest $request
    ): PipelineStatus {
        return $this->creator->create(
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Update an existing pipeline status.
     */
    public function update(
        UpdatePipelineStatusRequest $request,
        PipelineStatus $pipelineStatus
    ): PipelineStatus {
        return $this->updater->update(
            $pipelineStatus,
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Soft delete a pipeline status.
     */
    public function destroy(
        PipelineStatus $pipelineStatus,
        User $actor
    ): void {
        $this->destructor->delete($pipelineStatus, $actor->id);
    }

    /**
     * Restore a soft-deleted pipeline status.
     */
    public function restore(
        int $id,
        User $actor
    ): PipelineStatus {
        $pipelineStatus = PipelineStatus::withTrashed()->findOrFail($id);

        return $this->restorer->restore($pipelineStatus, $actor->id);
    }

    /**
     * Force delete a pipeline status, permanently removing it from the
     * database.
     */
    public function forceDelete(
        int $id,
        User $actor
    ): void {
        $pipelineStatus = PipelineStatus::withTrashed()->findOrFail($id);
        $this->destructor->forceDelete($pipelineStatus, $actor->id);
    }

    /**
     * Bulk restore pipeline statuses.
     */
    public function bulkRestore(
        array $ids,
        User $actor,
        callable $authoriseCallback
    ): array {
        $requestedIds = collect($ids)->unique()->values();

        $pipelineStatuses = PipelineStatus::onlyTrashed()
            ->whereIn('id', $requestedIds)
            ->get();

        $restored = [];

        foreach ($pipelineStatuses as $pipelineStatus) {
            /** @var PipelineStatus $pipelineStatus */
            $authoriseCallback($pipelineStatus);
            $this->restorer->restore($pipelineStatus, $actor->id);
            $restored[] = $pipelineStatus->id;
        }

        return [
            'restored' => $restored,
            'skipped' => $requestedIds
                ->diff($pipelineStatuses->pluck('id'))
                ->values()
                ->all(),
        ];
    }

    /**
     * Bulk soft delete pipeline statuses.
     */
    public function bulkDelete(
        array $ids,
        User $actor,
        callable $authoriseCallback
    ): array {
        $requestedIds = collect($ids)->unique()->values();

        $pipelineStatuses = PipelineStatus::whereIn('id', $requestedIds)->get();

        $deleted = [];

        foreach ($pipelineStatuses as $pipelineStatus) {
            /** @var PipelineStatus $pipelineStatus */
            $authoriseCallback($pipelineStatus);
            $this->destructor->delete($pipelineStatus, $actor->id);
            $deleted[] = $pipelineStatus->id;
        }

        return [
            'deleted' => $deleted,
            'skipped' => $requestedIds
                ->diff($pipelineStatuses->pluck('id'))
                ->values()
                ->all(),
        ];
    }

    /**
     * Import pipeline statuses from an uploaded file.
     *
     * @return array{imported: int, skipped: array<int, array{row: int, reason: string}>}
     */
    public function import(
        ImportPipelineStatusRequest $request
    ): array {
        return $this->importer->import(
            $request->file('file'),
            $request->user()->id
        );
    }

    /**
     * Export pipeline statuses matching the given filters as a CSV download.
     *
     * @param  array<string, mixed>  $filters
     */
    public function export(array $filters): StreamedResponse
    {
        return $this->exporter->export($filters);
    }
}
