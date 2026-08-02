<?php

namespace App\Services\InvoiceItems;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Log;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class ImporterService
{
    protected const REQUIRED_COLUMNS = [
        'description',
        'quantity',
        'unit_price',
        'tax_rate',
    ];

    /**
     * Inject the audit log service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
    ) {}

    /**
     * Import invoice items from an uploaded CSV file, scoped to a
     * single parent invoice.
     *
     * @return array{imported: int, skipped: array<int, array{row: int, reason: string}>}
     */
    public function import(
        UploadedFile $file,
        Invoice $invoice,
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
        $position = $invoice->items()->max('position') ?? 0;

        DB::transaction(function () use ($handle, $header, $invoice, $actor, $actorId, &$position, &$imported, &$skipped, &$rowNumber) {
            while (($row = fgetcsv($handle)) !== false) {
                $rowNumber++;

                $data = array_combine($header, $row);

                $error = $this->validateRow($data);

                if ($error !== null) {
                    $skipped[] = ['row' => $rowNumber, 'reason' => $error];

                    continue;
                }

                $quantity = (int) $data['quantity'];
                $unitPrice = (int) $data['unit_price'];
                $taxRate = (float) $data['tax_rate'];
                $total = (int) round($quantity * $unitPrice * (1 + $taxRate / 100));

                $position++;

                $item = InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => $data['description'],
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'tax_rate' => $taxRate,
                    'total' => $total,
                    'position' => $position,
                    'created_by' => $actorId,
                ]);

                $this->auditLogService->record(
                    Log::ACTION_IMPORT_INVOICE_ITEM,
                    $actor,
                    $item,
                    ['after' => $this->auditLogService->snapshot($item)],
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
            if (! isset($data[$column]) || $data[$column] === '') {
                return "Missing value for '{$column}'";
            }
        }

        if (! ctype_digit((string) $data['quantity']) || (int) $data['quantity'] < 1) {
            return 'quantity must be a positive integer';
        }

        if (! is_numeric($data['unit_price']) || (int) $data['unit_price'] < 0) {
            return 'unit_price must be a non-negative integer';
        }

        if (! is_numeric($data['tax_rate']) || (float) $data['tax_rate'] < 0) {
            return 'tax_rate must be a non-negative number';
        }

        return null;
    }
}
