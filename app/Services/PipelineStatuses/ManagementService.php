<?php

namespace App\Services\PipelineStatuses;

use App\Http\Requests\PipelineStatuses\StorePipelineStatusRequest;
use App\Http\Requests\PipelineStatuses\UpdatePipelineStatusRequest;
use App\Models\PipelineStatus;
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
        $deleted = [];

        foreach ($ids as $id) {
            $pipelineStatus = PipelineStatus::findOrFail($id);
            $authoriseCallback($pipelineStatus);

            $this->destructor->delete($pipelineStatus, $actor->id);
            $deleted[] = $id;
        }

        return $deleted;
    }
}
