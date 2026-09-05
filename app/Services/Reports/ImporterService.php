<?php

namespace App\Services\Reports;

use App\Models\Log;
use App\Models\Report;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class ImporterService
{
    protected const REQUIRED_COLUMNS = ['title', 'type', 'format'];

    /**
     * Inject the required services into the importer service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
        protected readonly ReportTypeRegistryService $registry,
    ) {}

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

                if (empty($data['title'])) {
                    $skipped[] = ['row' => $rowNumber, 'reason' => "Missing value for 'title'"];

                    continue;
                }

                if ($this->registry->queryServiceForKey($data['type'] ?? '') === null) {
                    $skipped[] = ['row' => $rowNumber, 'reason' => "Unrecognised 'type'"];

                    continue;
                }

                if (! in_array($data['format'] ?? '', ['pdf', 'csv', 'xlsx'], true)) {
                    $skipped[] = ['row' => $rowNumber, 'reason' => "Unrecognised 'format'"];

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
}
