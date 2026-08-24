<?php

namespace App\Services\Activities;

use App\Http\Requests\Activities\StoreActivityRequest;
use App\Http\Requests\Activities\UpdateActivityRequest;
use App\Models\Activity;
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
     * Create a new activity.
     */
    public function store(
        StoreActivityRequest $request
    ): Activity {
        return $this->creator->create(
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Update an existing activity.
     */
    public function update(
        UpdateActivityRequest $request,
        Activity $activity
    ): Activity {
        return $this->updater->update(
            $activity,
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Soft delete a activity.
     */
    public function destroy(
        Activity $activity,
        User $actor
    ): void {
        $this->destructor->delete($activity, $actor->id);
    }

    /**
     * Restore a soft-deleted activity.
     */
    public function restore(
        int $id,
        User $actor
    ): Activity {
        $activity = Activity::withTrashed()->findOrFail($id);

        return $this->restorer->restore($activity, $actor->id);
    }

    /**
     * Force delete a activity, permanently removing it from the
     * database.
     */
    public function forceDelete(
        int $id,
        User $actor
    ): void {
        $activity = Activity::withTrashed()->findOrFail($id);
        $this->destructor->forceDelete($activity, $actor->id);
    }

    /**
     * Bulk restore activities.
     */
    public function bulkRestore(
        array $ids,
        User $actor,
        callable $authoriseCallback
    ): array {
        $requestedIds = collect($ids)->unique()->values();
        $activities = Activity::onlyTrashed()->whereIn('id', $requestedIds)->get();

        $restored = [];

        foreach ($activities as $activity) {
            /** @var Activity $activity */
            $authoriseCallback($activity);
            $this->restorer->restore($activity, $actor->id);
            $restored[] = $activity->id;
        }

        return [
            'restored' => $restored,
            'skipped' => $requestedIds
                ->diff($activities->pluck('id'))
                ->values()
                ->all(),
        ];
    }

    /**
     * Bulk soft delete activities.
     */
    public function bulkDelete(
        array $ids,
        User $actor,
        callable $authoriseCallback
    ): array {
        $requestedIds = collect($ids)->unique()->values();
        $activities = Activity::whereIn('id', $requestedIds)->get();

        $deleted = [];

        foreach ($activities as $activity) {
            /** @var Activity $activity */
            $authoriseCallback($activity);
            $this->destructor->delete($activity, $actor->id);
            $deleted[] = $activity->id;
        }

        return [
            'deleted' => $deleted,
            'skipped' => $requestedIds
                ->diff($activities->pluck('id'))
                ->values()
                ->all(),
        ];
    }

    public function export(array $filters): StreamedResponse
    {
        return $this->exporter->export(
            $filters['activityable_type'] ?? null,
            $filters['activityable_id'] ?? null,
            $filters,
        );
    }

    /**
     * Records a system-generated entry (e.g. a status change surfaced by
     * another module's UpdaterService). Bypasses the FormRequest path
     * since there's no HTTP request behind it.
     */
    public function log(string $activityableType, int $activityableId, string $type, ?string $description, int $createdBy, array $meta = []): Activity
    {
        return $this->creator->create([
            'activityable_type' => $activityableType,
            'activityable_id' => $activityableId,
            'type' => $type,
            'description' => $description,
            'meta' => $meta,
        ], $createdBy);
    }
}
