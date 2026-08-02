<?php

namespace App\Services\Invoices;

use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceStatus;
use App\Models\Log;
use App\Models\Order;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class ImporterService
{
    protected const REQUIRED_COLUMNS = [
        'invoice_number',
        'subtotal',
        'tax_total',
        'total',
        'currency',
    ];

    /**
     * Inject the audit log service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
    ) {}

    /**
     * Import invoices from an uploaded CSV file.
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

                $invoice = Invoice::create([
                    'invoice_number' => $data['invoice_number'],
                    'company_id' => $this->nullableInt($data['company_id'] ?? null),
                    'order_id' => $this->nullableInt($data['order_id'] ?? null),
                    'status_id' => $this->nullableInt($data['status_id'] ?? null),
                    'issue_date' => $data['issue_date'] ?? null,
                    'due_date' => $data['due_date'] ?? null,
                    'subtotal' => (int) $data['subtotal'],
                    'tax_total' => (int) $data['tax_total'],
                    'total' => (int) $data['total'],
                    'currency' => strtoupper(trim($data['currency'])),
                    'notes' => $data['notes'] ?? null,
                    'created_by' => $actorId,
                ]);

                $this->auditLogService->record(
                    Log::ACTION_IMPORT_INVOICE,
                    $actor,
                    $invoice,
                    ['after' => $this->auditLogService->snapshot($invoice)],
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

        if (Invoice::withTrashed()->where('invoice_number', $data['invoice_number'])->exists()) {
            return "invoice_number '{$data['invoice_number']}' already exists";
        }

        foreach (['subtotal', 'tax_total', 'total'] as $column) {
            if (! is_numeric($data[$column]) || (int) $data[$column] < 0) {
                return "{$column} must be a non-negative integer";
            }
        }

        if (! preg_match('/^[A-Za-z]{3}$/', trim($data['currency']))) {
            return 'currency must be a 3-letter code (e.g. GBP)';
        }

        foreach ([
            'company_id' => Company::class,
            'order_id' => Order::class,
            'status_id' => InvoiceStatus::class,
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
