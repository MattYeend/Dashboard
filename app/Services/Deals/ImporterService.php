<?php

namespace App\Services\Deals;

use App\Models\Company;
use App\Models\Deal;
use App\Models\DealStatus;
use App\Models\Invoice;
use App\Models\Log;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class ImporterService
{
    protected const REQUIRED_COLUMNS = [
        'title',
        'value',
        'currency',
    ];

    /**
     * Inject the audit log service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
    ) {}

    /**
     * Import deals from an uploaded CSV file.
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

                $data = array_combine($header, $row);

                $error = $this->validateRow($data);

                if ($error !== null) {
                    $skipped[] = ['row' => $rowNumber, 'reason' => $error];

                    continue;
                }

                $deal = Deal::create([
                    'title' => $data['title'],
                    'description' => $data['description'] ?? null,
                    'pipeline_id' => $this->nullableInt($data['pipeline_id'] ?? null),
                    'stage_id' => $this->nullableInt($data['stage_id'] ?? null),
                    'status_id' => $this->nullableInt($data['status_id'] ?? null),
                    'company_id' => $this->nullableInt($data['company_id'] ?? null),
                    'invoice_id' => $this->nullableInt($data['invoice_id'] ?? null),
                    'value' => (int) $data['value'],
                    'currency' => strtoupper(trim($data['currency'])),
                    'probability' => isset($data['probability']) && $data['probability'] !== ''
                        ? (int) $data['probability']
                        : 0,
                    'expected_close_date' => $data['expected_close_date'] ?? null,
                    'closed_at' => $data['closed_at'] ?? null,
                    'created_by' => $actorId,
                ]);

                $this->auditLogService->record(
                    Log::ACTION_IMPORT_DEAL,
                    $actor,
                    $deal,
                    ['after' => $this->auditLogService->snapshot($deal)],
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

        if (! is_numeric($data['value']) || (int) $data['value'] < 0) {
            return 'value must be a non-negative integer';
        }

        if (! preg_match('/^[A-Za-z]{3}$/', trim($data['currency']))) {
            return 'currency must be a 3-letter code (e.g. GBP)';
        }

        foreach ([
            'pipeline_id' => Pipeline::class,
            'stage_id' => PipelineStage::class,
            'status_id' => DealStatus::class,
            'company_id' => Company::class,
            'invoice_id' => Invoice::class,
        ] as $column => $model) {
            if (empty($data[$column])) {
                continue;
            }

            if (! ctype_digit((string) $data[$column])) {
                return "{$column} must be a positive integer";
            }

            if (! $model::whereKey((int) $data[$column])->exists()) {
                return "{$column} '{$data[$column]}' does not exist";
            }
        }

        if (isset($data['probability']) && $data['probability'] !== '') {
            if (! ctype_digit((string) $data['probability']) || (int) $data['probability'] > 100) {
                return 'probability must be an integer between 0 and 100';
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
