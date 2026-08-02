<?php

namespace App\Services\PipelineStages;

use App\Http\Requests\PipelineStages\ImportPipelineStageRequest;
use App\Http\Requests\PipelineStages\StorePipelineStageRequest;
use App\Http\Requests\PipelineStages\UpdatePipelineStageRequest;
use App\Models\Pipeline;
use App\Models\PipelineStage;
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
     * Create a new pipeline stage.
     */
    public function store(
        StorePipelineStageRequest $request,
        Pipeline $pipeline
    ): PipelineStage {
        return $this->creator->create(
            $request->validated(),
            $pipeline->id,
            $request->user()->id
        );
    }

    /**
     * Update an existing pipeline stage.
     */
    public function update(
        UpdatePipelineStageRequest $request,
        PipelineStage $pipelineStage
    ): PipelineStage {
        return $this->updater->update(
            $pipelineStage,
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Soft delete a pipeline stage.
     */
    public function destroy(
        PipelineStage $pipelineStage,
        User $actor
    ): void {
        $this->destructor->delete($pipelineStage, $actor->id);
    }

    /**
     * Restore a soft-deleted pipeline stage.
     */
    public function restore(
        int $id,
        User $actor
    ): PipelineStage {
        $pipelineStage = PipelineStage::withTrashed()->findOrFail($id);

        return $this->restorer->restore($pipelineStage, $actor->id);
    }

    /**
     * Force delete a pipeline stage, permanently removing it from the
     * database.
     */
    public function forceDelete(
        int $id,
        User $actor
    ): void {
        $pipelineStage = PipelineStage::withTrashed()->findOrFail($id);
        $this->destructor->forceDelete($pipelineStage, $actor->id);
    }

    /**
     * Bulk restore pipeline stages.
     */
    public function bulkRestore(
        Pipeline $pipeline,
        array $ids,
        User $actor,
        callable $authoriseCallback
    ): array {
        $requestedIds = collect($ids)->unique()->values();

        $pipelineStages = $pipeline->stages()
            ->onlyTrashed()
            ->whereIn('id', $requestedIds)
            ->get();

        $restored = [];

        foreach ($pipelineStages as $pipelineStage) {
            /** @var PipelineStage $pipelineStage */
            $authoriseCallback($pipelineStage);
            $this->restorer->restore($pipelineStage, $actor->id);
            $restored[] = $pipelineStage->id;
        }

        return [
            'restored' => $restored,
            'skipped' => $requestedIds
                ->diff($pipelineStages->pluck('id'))
                ->values()
                ->all(),
        ];
    }

    /**
     * Bulk soft delete pipeline stages.
     */
    public function bulkDelete(
        Pipeline $pipeline,
        array $ids,
        User $actor,
        callable $authoriseCallback
    ): array {
        $requestedIds = collect($ids)->unique()->values();

        $pipelineStages = $pipeline->stages()
            ->whereIn('id', $requestedIds)
            ->get();

        $deleted = [];

        foreach ($pipelineStages as $pipelineStage) {
            /** @var PipelineStage $pipelineStage */
            $authoriseCallback($pipelineStage);
            $this->destructor->delete($pipelineStage, $actor->id);
            $deleted[] = $pipelineStage->id;
        }

        return [
            'deleted' => $deleted,
            'skipped' => $requestedIds
                ->diff($pipelineStages->pluck('id'))
                ->values()
                ->all(),
        ];
    }

    /**
     * Import pipeline stages from an uploaded file, scoped to one pipeline.
     *
     * @return array{imported: int, skipped: array<int, array{row: int, reason: string}>}
     */
    public function import(
        ImportPipelineStageRequest $request,
        Pipeline $pipeline
    ): array {
        return $this->importer->import(
            $request->file('file'),
            $pipeline,
            $request->user()->id
        );
    }

    /**
     * Export a single pipeline's stages matching the given filters as a CSV download.
     *
     * @param  array<string, mixed>  $filters
     */
    public function export(Pipeline $pipeline, array $filters): StreamedResponse
    {
        return $this->exporter->export($pipeline, $filters);
    }
}
