<?php

namespace App\Services\PipelineStages;

use App\Http\Requests\PipelineStages\StorePipelineStageRequest;
use App\Http\Requests\PipelineStages\UpdatePipelineStageRequest;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\User;

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
        array $ids,
        User $actor,
        callable $authoriseCallback
    ): array {
        $requestedIds = collect($ids)->unique()->values();

        $pipelineStages = PipelineStage::onlyTrashed()
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
        array $ids,
        User $actor,
        callable $authoriseCallback
    ): array {
        $deleted = [];

        foreach ($ids as $id) {
            $pipelineStage = PipelineStage::findOrFail($id);
            $authoriseCallback($pipelineStage);

            $this->destructor->delete($pipelineStage, $actor->id);
            $deleted[] = $id;
        }

        return $deleted;
    }
}
