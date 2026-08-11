<?php

namespace App\Services\Labels;

use App\Http\Requests\Labels\ImportLabelRequest;
use App\Http\Requests\Labels\StoreLabelRequest;
use App\Http\Requests\Labels\UpdateLabelRequest;
use App\Models\Label;
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
        protected readonly DeleterService $deleter,
        protected readonly RestorerService $restorer,
        protected readonly ImporterService $importer,
        protected readonly ExporterService $exporter,
    ) {}

    /**
     * Create a new label.
     */
    public function store(
        StoreLabelRequest $request
    ): Label {
        return $this->creator->create(
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Update an existing label.
     */
    public function update(
        UpdateLabelRequest $request,
        Label $label
    ): Label {
        return $this->updater->update(
            $label,
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Soft delete a label.
     */
    public function destroy(
        Label $label,
        User $actor
    ): void {
        $this->deleter->delete($label, $actor->id);
    }

    /**
     * Restore a soft-deleted label.
     */
    public function restore(
        int $id,
        User $actor
    ): Label {
        $label = Label::withTrashed()->findOrFail($id);

        return $this->restorer->restore($label, $actor->id);
    }

    /**
     * Force delete a label, permanently removing it from the database.
     */
    public function forceDelete(
        int $id,
        User $actor
    ): void {
        $label = Label::withTrashed()->findOrFail($id);
        $this->deleter->forceDelete($label, $actor->id);
    }

    /**
     * Bulk restore labels.
     */
    public function bulkRestore(
        array $ids,
        User $actor,
        callable $authoriseCallback
    ): array {
        $requestedIds = collect($ids)->unique()->values();

        $labels = Label::onlyTrashed()
            ->whereIn('id', $requestedIds)
            ->get();

        $restored = [];

        foreach ($labels as $label) {
            /** @var Label $label */
            $authoriseCallback($label);
            $this->restorer->restore($label, $actor->id);
            $restored[] = $label->id;
        }

        return [
            'restored' => $restored,
            'skipped' => $requestedIds
                ->diff($labels->pluck('id'))
                ->values()
                ->all(),
        ];
    }

    /**
     * Bulk soft delete labels.
     */
    public function bulkDelete(
        array $ids,
        User $actor,
        callable $authoriseCallback
    ): array {
        $requestedIds = collect($ids)->unique()->values();

        $labels = Label::whereIn('id', $requestedIds)->get();

        $deleted = [];

        foreach ($labels as $label) {
            /** @var Label $label */
            $authoriseCallback($label);
            $this->deleter->delete($label, $actor->id);
            $deleted[] = $label->id;
        }

        return [
            'deleted' => $deleted,
            'skipped' => $requestedIds
                ->diff($labels->pluck('id'))
                ->values()
                ->all(),
        ];
    }

    /**
     * Import labels from an uploaded file.
     *
     * @return array{imported: int, skipped: array<int, array{row: int, reason: string}>}
     */
    public function import(
        ImportLabelRequest $request
    ): array {
        return $this->importer->import(
            $request->file('file'),
            $request->user()->id
        );
    }

    /**
     * Export labels matching the given filters as a CSV download.
     *
     * @param  array<string, mixed>  $filters
     */
    public function export(array $filters): StreamedResponse
    {
        return $this->exporter->export($filters);
    }
}
