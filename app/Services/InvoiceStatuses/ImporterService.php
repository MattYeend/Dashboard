<?php

namespace App\Services\InvoiceStatuses;

use App\Models\InvoiceStatus;
use App\Models\Log;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class ImporterService
{
    protected const REQUIRED_COLUMNS = [
        'title',
        'background_colour',
        'text_colour',
    ];

    protected const HEX_COLOUR_PATTERN = '/^#[0-9A-Fa-f]{6}$/';

    /**
     * Inject the audit log service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
    ) {}

    /**
     * Import invoice statuses from an uploaded CSV file.
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

                $invoiceStatus = InvoiceStatus::create([
                    'title' => $data['title'],
                    'description' => $data['description'] ?? null,
                    'background_colour' => strtoupper(trim($data['background_colour'])),
                    'text_colour' => strtoupper(trim($data['text_colour'])),
                    'created_by' => $actorId,
                ]);

                $this->auditLogService->record(
                    Log::ACTION_IMPORT_INVOICE_STATUS,
                    $actor,
                    $invoiceStatus,
                    ['after' => $this->auditLogService->snapshot($invoiceStatus)],
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

        if (! preg_match(self::HEX_COLOUR_PATTERN, trim($data['background_colour']))) {
            return 'background_colour must be a 6-digit hex colour (e.g. #FFFFFF)';
        }

        if (! preg_match(self::HEX_COLOUR_PATTERN, trim($data['text_colour']))) {
            return 'text_colour must be a 6-digit hex colour (e.g. #000000)';
        }

        return null;
    }
}
