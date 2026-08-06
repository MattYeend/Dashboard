<?php

namespace App\Services\Pipelines;

use App\Models\Log;
use App\Models\Pipeline;
use App\Models\PipelineStatus;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class ImporterService
{
    protected const REQUIRED_COLUMNS = [
        'title',
    ];

    /**
     * Inject the audit log service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
    ) {}

    /**
     * Import pipelines from an uploaded CSV file.
     *
     * @return array{imported: int, skipped: array<int, array{row: int, reason: string}>}
     */
    public function import(
        UploadedFile $file,
        int $actorId
    ): array {
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
                        'reason' => sprintf(
                            'Expected %d columns but found %d',
                            count($header),
                            count($row),
                        ),
                    ];

                    continue;
                }

                $data = array_combine($header, $row);

                $error = $this->validateRow($data);

                if ($error !== null) {
                    $skipped[] = ['row' => $rowNumber, 'reason' => $error];

                    continue;
                }

                $pipeline = Pipeline::create([
                    'title' => $data['title'],
                    'description' => $data['description'] ?? null,
                    'is_default' => filter_var($data['is_default'] ?? false, FILTER_VALIDATE_BOOLEAN),
                    'status_id' => $this->nullableInt($data['status_id'] ?? null),
                    'assigned_to' => $this->nullableInt($data['assigned_to'] ?? null),
                    'created_by' => $actorId,
                ]);

                $this->auditLogService->record(
                    Log::ACTION_IMPORT_PIPELINE,
                    $actor,
                    $pipeline,
                    ['after' => $this->auditLogService->snapshot($pipeline)],
                );

                $imported++;
            }
        });

        fclose($handle);

        return ['imported' => $imported, 'skipped' => $skipped];
    }

    /**
     * Validate a single row, returning an error string or null if valid.
     */
    private function validateRow(array $data): ?string
    {
        foreach (self::REQUIRED_COLUMNS as $column) {
            if (empty($data[$column])) {
                return "Missing value for '{$column}'";
            }
        }

        if (! empty($data['status_id'])) {
            if (! ctype_digit((string) $data['status_id'])) {
                return 'status_id must be a positive integer';
            }

            if (! PipelineStatus::whereKey((int) $data['status_id'])->exists()) {
                return "status_id '{$data['status_id']}' does not exist";
            }
        }

        if (! empty($data['assigned_to'])) {
            if (! ctype_digit((string) $data['assigned_to'])) {
                return 'assigned_to must be a positive integer';
            }

            if (! User::whereKey((int) $data['assigned_to'])->exists()) {
                return "assigned_to '{$data['assigned_to']}' does not exist";
            }
        }

        return null;
    }

    /**
     * Cast a CSV value to a nullable integer.
     */
    private function nullableInt(?string $value): ?int
    {
        return $value === null || $value === '' ? null : (int) $value;
    }
}
