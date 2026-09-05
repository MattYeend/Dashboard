<?php

namespace App\Services\Reports;

use App\Models\Log;
use App\Models\Report;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class RunnerService
{
    /**
     * Inject the required services into the runner service.
     */
    public function __construct(
        protected readonly ReportTypeRegistryService $registry,
        protected readonly AuditLogService $auditLogService,
    ) {}

    /**
     * Generate the report's output file from its underlying dataset and
     * write it to storage, returning the stored path.
     *
     * @throws InvalidArgumentException
     */
    public function run(Report $report, User $actor): string
    {
        $rows = $this->resolveDataset($report);
        $path = $this->writeOutput($report, $rows);

        $report->forceFill(['last_run_at' => now()])->save();

        $this->auditLogService->record(
            Log::ACTION_RUN_REPORT,
            $actor,
            $report,
            ['after' => ['path' => $path]],
        );

        return $path;
    }

    /**
     * Resolve the underlying dataset for the report via its allow-listed
     * QueryService, applying the report's stored filters.
     *
     * @return array<int, array<string, mixed>>
     *
     * @throws InvalidArgumentException
     */
    private function resolveDataset(Report $report): array
    {
        $queryServiceClass = $this->registry->queryServiceForKey($report->type);

        if ($queryServiceClass === null) {
            throw new InvalidArgumentException("Unrecognised report type: {$report->type}");
        }

        $result = app($queryServiceClass)->getPaginated(
            $report->creator,
            array_merge($report->filters ?? [], ['per_page' => 1000])
        );

        $firstKey = array_key_first($result);

        return array_values($result[$firstKey]['data'] ?? []);
    }

    /**
     * Write the dataset to storage in the report's configured format.
     */
    private function writeOutput(Report $report, array $rows): string
    {
        $path = "reports/{$report->id}/".now()->format('Y-m-d-His').'.'.$report->format;

        Storage::put($path, match ($report->format) {
            'csv' => $this->toCsv($rows),
            default => json_encode($rows),
        });

        return $path;
    }

    /**
     * Convert an array of associative rows to CSV.
     */
    private function toCsv(array $rows): string
    {
        if (empty($rows)) {
            return '';
        }

        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, array_keys($rows[0]));

        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv;
    }
}