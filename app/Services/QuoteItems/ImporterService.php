<?php

declare(strict_types=1);

namespace App\Services\QuoteItems;

use App\Models\Log;
use App\Models\Product;
use App\Models\Quote;
use App\Services\AuditLogService;
use App\Services\Concerns\ImportsViaPreview;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use LogicException;

/**
 * Handles CSV import of quote lines into a single quote.
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
        'name',
        'quantity',
        'unit_price',
    ];

    /**
     * The quote the file is being imported into.
     */
    protected ?Quote $quote = null;

    /**
     * Inject the required services.
     */
    public function __construct(
        protected readonly CreatorService $creator,
        protected readonly AuditLogService $auditLogService,
    ) {}

    /**
     * Scope this importer to a quote. Must be called before preview,
     * commit or import.
     */
    public function forQuote(Quote $quote): static
    {
        $this->quote = $quote->loadMissing('status');

        return $this;
    }

    protected function importStoragePath(): string
    {
        return 'imports/quote-items';
    }

    protected function importAuditAction(): int
    {
        return Log::ACTION_IMPORT_QUOTE_ITEM;
    }

    /**
     * Persist a single validated row as a new line on the scoped quote.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $context
     */
    protected function persistRow(array $data, int $actorId, array $context = []): void
    {
        $this->creator->create($this->scopedQuote(), $this->attributesFromRow($data), $actorId);
    }

    /**
     * Import lines from an uploaded CSV file.
     *
     * @return array{imported: int, skipped: array<int, array{row: int, reason: string}>}
     */
    public function import(UploadedFile $file, int $actorId): array
    {
        $quote = $this->scopedQuote();
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

        DB::transaction(function () use ($handle, $header, $quote, $actorId, &$imported, &$skipped, &$rowNumber) {
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

                try {
                    $this->creator->create($quote, $this->attributesFromRow($data), $actorId);
                    $imported++;
                } catch (\Illuminate\Validation\ValidationException $exception) {
                    $skipped[] = ['row' => $rowNumber, 'reason' => collect($exception->errors())->flatten()->first()];
                }
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

        if (mb_strlen(trim((string) $data['name'])) > 255) {
            return 'name may not exceed 255 characters';
        }

        $quantity = trim((string) $data['quantity']);

        if (! preg_match('/^\d{1,8}(\.\d{1,2})?$/', $quantity) || bccomp($quantity, '0', 2) !== 1) {
            return 'quantity must be greater than zero with up to 2 decimal places';
        }

        foreach (['unit_price'] as $column) {
            if (! preg_match('/^\d{1,8}(\.\d{1,2})?$/', trim((string) $data[$column]))) {
                return "{$column} must be a non-negative amount with up to 2 decimal places";
            }
        }

        foreach (['discount_percent', 'tax_rate'] as $column) {
            $value = trim((string) ($data[$column] ?? ''));

            if ($value !== '' && (
                ! preg_match('/^\d{1,3}(\.\d{1,2})?$/', $value)
                || bccomp($value, '100', 2) === 1
            )) {
                return "{$column} must be between 0 and 100";
            }
        }

        $unit = trim((string) ($data['unit'] ?? ''));

        if ($unit !== '' && ! in_array($unit, Product::UNITS, true)) {
            return "unit '{$unit}' is not permitted";
        }

        $sku = trim((string) ($data['sku'] ?? ''));

        if ($sku !== '' && ! Product::where('sku', strtoupper($sku))->where('is_active', true)->exists()) {
            return "No active product with SKU '{$sku}'";
        }

        return null;
    }

    /**
     * Build the attributes passed to CreatorService from a validated row.
     * A SKU, when present, links the line to a product in this tenant.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function attributesFromRow(array $data): array
    {
        $sku = trim((string) ($data['sku'] ?? ''));
        $description = trim((string) ($data['description'] ?? ''));
        $unit = trim((string) ($data['unit'] ?? ''));
        $discount = trim((string) ($data['discount_percent'] ?? ''));
        $taxRate = trim((string) ($data['tax_rate'] ?? ''));

        return array_filter([
            'product_id' => $sku === '' ? null : Product::where('sku', strtoupper($sku))->value('id'),
            'name' => trim((string) $data['name']),
            'description' => $description === '' ? null : $description,
            'unit' => $unit === '' ? null : $unit,
            'quantity' => trim((string) $data['quantity']),
            'unit_price' => trim((string) $data['unit_price']),
            'discount_percent' => $discount === '' ? null : $discount,
            'tax_rate' => $taxRate === '' ? null : $taxRate,
        ], fn ($value) => $value !== null);
    }

    /**
     * Return the scoped quote or fail loudly.
     *
     * @throws LogicException
     */
    private function scopedQuote(): Quote
    {
        return $this->quote ?? throw new LogicException('Call forQuote() before importing quote lines.');
    }
}