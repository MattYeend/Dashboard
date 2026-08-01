<?php

namespace App\Services\Industries;

use App\Http\Requests\Industries\ImportIndustryRequest;
use App\Http\Requests\Industries\StoreIndustryRequest;
use App\Http\Requests\Industries\UpdateIndustryRequest;
use App\Models\Industry;
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
     * Create a new industry.
     */
    public function store(
        StoreIndustryRequest $request
    ): Industry {
        return $this->creator->create(
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Update an existing industry.
     */
    public function update(
        UpdateIndustryRequest $request,
        Industry $industry
    ): Industry {
        return $this->updater->update(
            $industry,
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Soft delete a industry.
     */
    public function destroy(
        Industry $industry,
        User $actor
    ): void {
        $this->destructor->delete($industry, $actor->id);
    }

    /**
     * Restore a soft-deleted industry.
     */
    public function restore(
        int $id,
        User $actor
    ): Industry {
        $industry = Industry::withTrashed()->findOrFail($id);

        return $this->restorer->restore($industry, $actor->id);
    }

    /**
     * Force delete a industry, permanently removing it from the database.
     */
    public function forceDelete(
        int $id,
        User $actor
    ): void {
        $industry = Industry::withTrashed()->findOrFail($id);
        $this->destructor->forceDelete($industry, $actor->id);
    }

    /**
     * Bulk restore industries.
     */
    public function bulkRestore(
        array $ids,
        User $actor,
        callable $authoriseCallback
    ): array {
        $requestedIds = collect($ids)->unique()->values();

        $industries = Industry::onlyTrashed()
            ->whereIn('id', $requestedIds)
            ->get();

        $restored = [];

        foreach ($industries as $industry) {
            /** @var Industry $industry */
            $authoriseCallback($industry);
            $this->restorer->restore($industry, $actor->id);
            $restored[] = $industry->id;
        }

        return [
            'restored' => $restored,
            'skipped' => $requestedIds
                ->diff($industries->pluck('id'))
                ->values()
                ->all(),
        ];
    }

    /**
     * Bulk soft delete industries.
     */
    public function bulkDelete(
        array $ids,
        User $actor,
        callable $authoriseCallback
    ): array {
        $requestedIds = collect($ids)->unique()->values();

        $industries = Industry::whereIn('id', $requestedIds)->get();

        $deleted = [];

        foreach ($industries as $industry) {
            /** @var Industry $industry */
            $authoriseCallback($industry);
            $this->destructor->delete($industry, $actor->id);
            $deleted[] = $industry->id;
        }

        return [
            'deleted' => $deleted,
            'skipped' => $requestedIds
                ->diff($industries->pluck('id'))
                ->values()
                ->all(),
        ];
    }

    /**
     * Import industries from an uploaded file.
     *
     * @return array{imported: int, skipped: array<int, array{row: int, reason: string}>}
     */
    public function import(
        ImportIndustryRequest $request
    ): array {
        return $this->importer->import(
            $request->file('file'),
            $request->user()->id
        );
    }

    /**
     * Export industries matching the given filters as a CSV download.
     *
     * @param  array<string, mixed>  $filters
     */
    public function export(array $filters): StreamedResponse
    {
        return $this->exporter->export($filters);
    }
}