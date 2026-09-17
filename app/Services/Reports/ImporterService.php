<?php

namespace App\Services\Reports;

use App\Models\Log;
use App\Models\Report;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\Concerns\ImportsViaPreview;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * Handles CSV import of reports, including the newer preview/commit
 * workflow (via ImportsViaPreview) alongside the original one-shot
 * import() method.
 */
class ImporterService
{
    use ImportsViaPreview;

    /**
     * Column headers that must be present in the uploaded CSV.
     *
     * @var array<int, string>
     */
    protected const REQUIRED_COLUMNS = ['title', 'type', 'format'];

    /**
     * Inject the required services into the importer service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
        protected readonly ReportTypeRegistryService $registry,
    ) {}

    /**
     * The private-disk directory this module's uploads are stored under.
     */
    protected function importStoragePath(): string
    {
        return 'imports/reports';
    }

    /**
     * The Log::ACTION_IMPORT_* constant for this module's batch log entry.
     */
    protected function importAuditAction(): int
    {
        return Log::ACTION_IMPORT_REPORT;
    }

    /**
     * Persist a single validated row as a new Report.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $context
     */
    protected function persistRow(array $data, int $actorId, array $context = []): void
    {
        Report::create([
            'title' => $data['title'],
            'type' => $data['type'],
            'format' => $data['format'],
            'is_scheduled' => false,
            'created_by' => $actorId,
        ]);
    }

    /**
     * Import reports from an uploaded CSV file.
     *
     * @return array{imported: int, skipped: array<int, array{row: int, reason: string}>}
     */
    public function import(UploadedFile $file, int $actorId): array
    {
        $handle = fopen($file->getRealPath(), 'r');

        $header = fgetcsv($handle);
        $header = array_map(fn (string $column) => strtolower(trim($column)), $header ?: []);

        $missing = array_diff(self::REQUIRED_COLUMNS, $header);

        if (! empty($missing)) {
            fclose($handle);

            return [
                'imported' => 0,
                'skipped' => [[
                    'row' => 0,
                    'reason' => 'Missing required column(s): '.implode(', ', $missing),
                ]],
            ];
        }

        $imported = 0;
        $skipped = [];
        $rowNumber = 1;
        $actor = User::findOrFail($actorId);

        DB::transaction(function () use ($handle, $header, $actor, $actorId, &$imported, &$skipped, &$rowNumber) {
            while (($row = fgetcsv($handle)) !== false) {
                $rowNumber++;

                if (count($row) !== count($header)) {
                    $skipped[] = [
                        'row' => $rowNumber,
                        'reason' => sprintf('Expected %d columns but found %d', count($header), count($row)),
                    ];

                    continue;
                }

                $data = array_combine($header, $row);

                $error = $this->validateRow($data);

                if ($error !== null) {
                    $skipped[] = ['row' => $rowNumber, 'reason' => $error];

                    continue;
                }

                $report = Report::create([
                    'title' => $data['title'],
                    'type' => $data['type'],
                    'format' => $data['format'],
                    'is_scheduled' => false,
                    'created_by' => $actorId,
                ]);

                $this->auditLogService->record(
                    Log::ACTION_IMPORT_REPORT,
                    $actor,
                    $report,
                    ['after' => $this->auditLogService->snapshot($report)],
                );

                $imported++;
            }
        });

        fclose($handle);

        return ['imported' => $imported, 'skipped' => $skipped];
    }

    /**
     * Validate a single row, returning an error string or null if valid.
     *
     * Extracted from the original inline validation inside import()'s
     * loop, so it can be reused by the preview/commit workflow too.
     *
     * @param  array<string, mixed>  $data
     */
    protected function validateRow(array $data): ?string
    {
        if (empty($data['title'])) {
            return "Missing value for 'title'";
        }

        if ($this->registry->queryServiceForKey($data['type'] ?? '') === null) {
            return "Unrecognised 'type'";
        }

        if (! in_array($data['format'] ?? '', ['pdf', 'csv', 'xlsx'], true)) {
            return "Unrecognised 'format'";
        }

        return null;
    }
}
