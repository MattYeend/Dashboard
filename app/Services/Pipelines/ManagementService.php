<?php

namespace App\Services\Pipelines;

use App\Http\Requests\Pipelines\ImportPipelineRequest;
use App\Http\Requests\Pipelines\StorePipelineRequest;
use App\Http\Requests\Pipelines\UpdatePipelineRequest;
use App\Models\Pipeline;
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
        StorePipelineRequest $request
    ): Pipeline {
        return $this->creator->create(
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Update an existing pipeline status.
     */
    public function update(
        UpdatePipelineRequest $request,
        Pipeline $pipeline
    ): Pipeline {
        return $this->updater->update(
            $pipeline,
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Soft delete a pipeline status.
     */
    public function destroy(
        Pipeline $pipeline,
        User $actor
    ): void {
        $this->destructor->delete($pipeline, $actor->id);
    }

    /**
     * Restore a soft-deleted pipeline status.
     */
    public function restore(
        int $id,
        User $actor
    ): Pipeline {
        $pipeline = Pipeline::withTrashed()->findOrFail($id);

        return $this->restorer->restore($pipeline, $actor->id);
    }

    /**
     * Force delete a pipeline status, permanently removing it from the
     * database.
     */
    public function forceDelete(
        int $id,
        User $actor
    ): void {
        $pipeline = Pipeline::withTrashed()->findOrFail($id);
        $this->destructor->forceDelete($pipeline, $actor->id);
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

        $pipelines = Pipeline::onlyTrashed()
            ->whereIn('id', $requestedIds)
            ->get();

        $restored = [];

        foreach ($pipelines as $pipeline) {
            /** @var Pipeline $pipeline */
            $authoriseCallback($pipeline);
            $this->restorer->restore($pipeline, $actor->id);
            $restored[] = $pipeline->id;
        }

        return [
            'restored' => $restored,
            'skipped' => $requestedIds
                ->diff($pipelines->pluck('id'))
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

        $pipelines = Pipeline::whereIn('id', $requestedIds)->get();

        $deleted = [];

        foreach ($pipelines as $pipeline) {
            /** @var Pipeline $pipeline */
            $authoriseCallback($pipeline);
            $this->destructor->delete($pipeline, $actor->id);
            $deleted[] = $pipeline->id;
        }

        return [
            'deleted' => $deleted,
            'skipped' => $requestedIds
                ->diff($pipelines->pluck('id'))
                ->values()
                ->all(),
        ];
    }

    /**
     * Import pipelines from an uploaded file.
     *
     * @return array{imported: int, skipped: array<int, array{row: int, reason: string}>}
     */
    public function import(
        ImportPipelineRequest $request
    ): array {
        return $this->importer->import(
            $request->file('file'),
            $request->user()->id
        );
    }

    /**
     * Export pipelines matching the given filters as a CSV download.
     *
     * @param  array<string, mixed>  $filters
     */
    public function export(array $filters): StreamedResponse
    {
        return $this->exporter->export($filters);
    }
}
