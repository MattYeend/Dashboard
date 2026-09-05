<?php

namespace App\Services\Reports;

use App\Http\Requests\Reports\ImportReportRequest;
use App\Http\Requests\Reports\StoreReportRequest;
use App\Http\Requests\Reports\UpdateReportRequest;
use App\Models\Report;
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
        protected readonly RunnerService $runner,
    ) {}

    /**
     * Create a new report.
     */
    public function store(StoreReportRequest $request): Report
    {
        return $this->creator->create($request->validated(), $request->user()->id);
    }

    /**
     * Update an existing report.
     */
    public function update(UpdateReportRequest $request, Report $report): Report
    {
        return $this->updater->update($report, $request->validated(), $request->user()->id);
    }

    /**
     * Delete a report.
     */
    public function destroy(Report $report, User $actor): void
    {
        $this->destructor->delete($report, $actor->id);
    }

    /**
     * Restore a soft-deleted report.
     */
    public function restore(int $id, User $actor): Report
    {
        $report = Report::onlyTrashed()->findOrFail($id);

        return $this->restorer->restore($report, $actor->id);
    }

    /**
     * Force delete a report, permanently removing it from the database.
     */
    public function forceDelete(int $id, User $actor): void
    {
        $report = Report::withTrashed()->findOrFail($id);
        $this->destructor->forceDelete($report, $actor->id);
    }

    /**
     * Bulk soft delete reports.
     *
     * @return array{deleted: array<int, int>, skipped: array<int, int>}
     */
    public function bulkDelete(array $ids, User $actor, callable $authoriseCallback): array
    {
        $requestedIds = collect($ids)->unique()->values();
        $reports = Report::whereIn('id', $requestedIds)->get();
        $deleted = [];

        foreach ($reports as $report) {
            /** @var Report $report */
            $authoriseCallback($report);
            $this->destructor->delete($report, $actor->id);
            $deleted[] = $report->id;
        }

        return [
            'deleted' => $deleted,
            'skipped' => $requestedIds->diff($reports->pluck('id'))->values()->all(),
        ];
    }

    /**
     * Bulk restore reports.
     *
     * @return array{restored: array<int, int>, skipped: array<int, int>}
     */
    public function bulkRestore(array $ids, User $actor, callable $authoriseCallback): array
    {
        $requestedIds = collect($ids)->unique()->values();
        $reports = Report::onlyTrashed()->whereIn('id', $requestedIds)->get();
        $restored = [];

        foreach ($reports as $report) {
            /** @var Report $report */
            $authoriseCallback($report);
            $this->restorer->restore($report, $actor->id);
            $restored[] = $report->id;
        }

        return [
            'restored' => $restored,
            'skipped' => $requestedIds->diff($reports->pluck('id'))->values()->all(),
        ];
    }

    /**
     * Import reports from an uploaded file.
     *
     * @return array{imported: int, skipped: array<int, array{row: int, reason: string}>}
     */
    public function import(ImportReportRequest $request): array
    {
        return $this->importer->import($request->file('file'), $request->user()->id);
    }

    /**
     * Export reports matching the given filters as a CSV download.
     *
     * @param  array<string, mixed>  $filters
     */
    public function export(array $filters): StreamedResponse
    {
        return $this->exporter->export($filters);
    }

    /**
     * Generate the given report's output file on demand.
     */
    public function run(Report $report, User $actor): string
    {
        return $this->runner->run($report, $actor);
    }
}
