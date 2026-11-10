<?php

declare(strict_types=1);

namespace App\Services\Products;

use App\Models\Log;
use App\Models\Product;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\Concerns\ImportsViaPreview;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * Handles CSV import of products, including the preview/commit
 * workflow (via ImportsViaPreview) alongside the one-shot import() method.
 */
class ImporterService
{
    use ImportsViaPreview;

    /**
     * Column headers that must be present in the uploaded CSV.
     *
     * @var array<int, string>
     */
    protected const REQUIRED_COLUMNS = [
        'sku',
        'name',
        'unit_price',
    ];

    /**
     * Inject the audit log service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
    ) {}

    /**
     * The private-disk directory this module's uploads are stored under.
     */
    protected function importStoragePath(): string
    {
        return 'imports/products';
    }

    /**
     * The Log::ACTION_IMPORT_* constant for this module's batch log entry.
     */
    protected function importAuditAction(): int
    {
        return Log::ACTION_IMPORT_PRODUCT;
    }

    /**
     * Persist a single validated row as a new Product.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $context
     */
    protected function persistRow(array $data, int $actorId, array $context = []): void
    {
        $sku = strtoupper(trim((string) $data['sku']));

        // Guards against the same SKU appearing twice in one file.
        if (Product::withTrashed()->where('sku', $sku)->exists()) {
            return;
        }

        Product::create([
            'sku' => $sku,
            'name' => trim((string) $data['name']),
            'description' => $this->nullableString($data['description'] ?? null),
            'unit' => $this->nullableString($data['unit'] ?? null) ?? 'each',
            'unit_price' => $data['unit_price'],
            'cost_price' => $this->nullableString($data['cost_price'] ?? null),
            'tax_rate' => $this->nullableString($data['tax_rate'] ?? null) ?? '20.00',
            'currency' => strtoupper($this->nullableString($data['currency'] ?? null) ?? 'GBP'),
            'is_active' => $this->parseBoolean($data['is_active'] ?? null),
            'created_by' => $actorId,
        ]);
    }

    /**
     * Import products from an uploaded CSV file.
     *
     * @return array{imported: int, skipped: array<int, array{row: int, reason: string}>}
     */
    public function import(
        UploadedFile $file,
        int $actorId
    ): array {
        $handle = fopen($file->getRealPath(), 'r');

        $header = fgetcsv($handle);
        $header = array_map(fn (?string $column) => strtolower(trim((string) $column)), $header ?: []);

        $missing = array_diff(self::REQUIRED_COLUMNS, $header);

        if ($missing !== []) {
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

                $sku = strtoupper(trim((string) $data['sku']));

                if (Product::withTrashed()->where('sku', $sku)->exists()) {
                    $skipped[] = ['row' => $rowNumber, 'reason' => "SKU '{$sku}' already exists"];

                    continue;
                }

                $product = Product::create([
                    'sku' => $sku,
                    'name' => trim((string) $data['name']),
                    'description' => $this->nullableString($data['description'] ?? null),
                    'unit' => $this->nullableString($data['unit'] ?? null) ?? 'each',
                    'unit_price' => $data['unit_price'],
                    'cost_price' => $this->nullableString($data['cost_price'] ?? null),
                    'tax_rate' => $this->nullableString($data['tax_rate'] ?? null) ?? '20.00',
                    'currency' => strtoupper($this->nullableString($data['currency'] ?? null) ?? 'GBP'),
                    'is_active' => $this->parseBoolean($data['is_active'] ?? null),
                    'created_by' => $actorId,
                ]);

                $this->auditLogService->record(
                    Log::ACTION_IMPORT_PRODUCT,
                    $actor,
                    $product,
                    ['after' => $this->auditLogService->snapshot($product)],
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
     * @param  array<string, mixed>  $data
     */
    protected function validateRow(array $data): ?string
    {
        foreach (self::REQUIRED_COLUMNS as $column) {
            if (trim((string) ($data[$column] ?? '')) === '') {
                return "Missing value for '{$column}'";
            }
        }

        $sku = trim((string) $data['sku']);

        if (strlen($sku) > 64 || ! preg_match('/^[A-Za-z0-9_\-]+$/', $sku)) {
            return 'sku may only contain letters, numbers, dashes and underscores (64 characters maximum)';
        }

        if (mb_strlen(trim((string) $data['name'])) > 255) {
            return 'name may not exceed 255 characters';
        }

        foreach (['unit_price', 'cost_price'] as $column) {
            $value = trim((string) ($data[$column] ?? ''));

            if ($value !== '' && ! preg_match('/^\d{1,8}(\.\d{1,2})?$/', $value)) {
                return "{$column} must be a non-negative amount with up to 2 decimal places";
            }
        }

        $taxRate = trim((string) ($data['tax_rate'] ?? ''));

        if ($taxRate !== '' && (
            ! preg_match('/^\d{1,3}(\.\d{1,2})?$/', $taxRate)
            || bccomp($taxRate, '100', 2) === 1
        )) {
            return 'tax_rate must be between 0 and 100';
        }

        $unit = trim((string) ($data['unit'] ?? ''));

        if ($unit !== '' && ! in_array($unit, Product::UNITS, true)) {
            return "unit '{$unit}' is not permitted";
        }

        $currency = strtoupper(trim((string) ($data['currency'] ?? '')));

        if ($currency !== '' && ! in_array($currency, config('currencies.allowed'), true)) {
            return "currency '{$currency}' is not permitted";
        }

        return null;
    }

    /**
     * Cast a CSV value to a trimmed nullable string.
     */
    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    /**
     * Cast a CSV value to a boolean, defaulting to true when blank.
     */
    private function parseBoolean(mixed $value): bool
    {
        $value = strtolower(trim((string) $value));

        if ($value === '') {
            return true;
        }

        return in_array($value, ['1', 'true', 'yes', 'y'], true);
    }
}
