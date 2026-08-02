<?php

namespace App\Services\Orders;

use App\Models\Log;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class ImporterService
{
    protected const REQUIRED_COLUMNS = [
        'orderable_type',
        'orderable_id',
        'title',
        'subtotal',
        'tax_amount',
        'total_amount',
    ];

    /**
     * Inject the type registry so the import allow-list stays in sync
     * with the single source of truth used elsewhere (e.g. form options).
     */
    public function __construct(
        protected readonly OrderableTypeRegistryService $typeRegistry,
        protected readonly AuditLogService $auditLogService,
    ) {}

    /**
     * Import orders from an uploaded CSV file.
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

                $order = Order::create([
                    'orderable_type' => $this->typeRegistry->modelClassForKey(
                        strtolower(trim($data['orderable_type']))
                    ),
                    'orderable_id' => (int) $data['orderable_id'],
                    'title' => $data['title'],
                    'description' => $data['description'] ?? null,
                    'notes' => $data['notes'] ?? null,
                    'subtotal' => $data['subtotal'],
                    'discount_amount' => $data['discount_amount'] ?? '0.00',
                    'tax_amount' => $data['tax_amount'],
                    'total_amount' => $data['total_amount'],
                    'ordered_at' => $data['ordered_at'] ?? null,
                    'due_at' => $data['due_at'] ?? null,
                    'status_id' => $this->nullableInt($data['status_id'] ?? null),
                    'created_by' => $actorId,
                ]);

                $this->auditLogService->record(
                    Log::ACTION_IMPORT_ORDER,
                    $actor,
                    $order,
                    ['after' => $this->auditLogService->snapshot($order)],
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

        $type = strtolower(trim($data['orderable_type']));

        if ($this->typeRegistry->modelClassForKey($type) === null) {
            return "'{$data['orderable_type']}' is not a permitted orderable type";
        }

        if (! ctype_digit((string) $data['orderable_id'])) {
            return 'orderable_id must be a positive integer';
        }

        foreach (['subtotal', 'discount_amount', 'tax_amount', 'total_amount'] as $column) {
            if (! isset($data[$column]) || $data[$column] === '') {
                continue;
            }

            if (! is_numeric($data[$column]) || (float) $data[$column] < 0) {
                return "{$column} must be a non-negative number";
            }
        }

        if (! empty($data['status_id'])) {
            if (! ctype_digit((string) $data['status_id'])) {
                return 'status_id must be a positive integer';
            }

            if (! OrderStatus::whereKey((int) $data['status_id'])->exists()) {
                return "status_id '{$data['status_id']}' does not exist";
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
