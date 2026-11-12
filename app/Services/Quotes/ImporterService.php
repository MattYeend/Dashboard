<?php

declare(strict_types=1);

namespace App\Services\Quotes;

use App\Models\Log;
use App\Models\Organisation;
use App\Models\Quote;
use App\Models\QuoteStatus;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\Concerns\ImportsViaPreview;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Handles CSV import of quotes, including the preview/commit workflow
 * (via ImportsViaPreview) alongside the one-shot import() method.
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
        'quotable_type',
        'quotable_id',
        'title',
        'subtotal',
        'tax_amount',
    ];

    /**
     * Inject the required services.
     */
    public function __construct(
        protected readonly QuotableTypeRegistryService $typeRegistry,
        protected readonly TotalsService $totals,
        protected readonly AuditLogService $auditLogService,
    ) {}

    protected function importStoragePath(): string
    {
        return 'imports/quotes';
    }

    protected function importAuditAction(): int
    {
        return Log::ACTION_IMPORT_QUOTE;
    }

    /**
     * Persist a single validated row as a new Quote.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $context
     */
    protected function persistRow(array $data, int $actorId, array $context = []): void
    {
        Quote::create($this->attributesFromRow($data, $actorId));
    }

    /**
     * Import quotes from an uploaded CSV file.
     *
     * @return array{imported: int, skipped: array<int, array{row: int, reason: string}>}
     */
    public function import(UploadedFile $file, int $actorId): array
    {
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

                $quote = Quote::create($this->attributesFromRow($data, $actorId));

                $this->auditLogService->record(
                    Log::ACTION_IMPORT_QUOTE,
                    $actor,
                    $quote,
                    ['after' => $this->auditLogService->snapshot($quote)],
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

        $type = strtolower(trim((string) $data['quotable_type']));
        $modelClass = $this->typeRegistry->modelClassForKey($type);

        if ($modelClass === null) {
            return "'{$data['quotable_type']}' is not a permitted customer type";
        }

        $id = trim((string) $data['quotable_id']);

        if (! ctype_digit($id) || (int) $id < 1) {
            return 'quotable_id must be a positive integer';
        }

        $exists = $modelClass === User::class
            ? (Organisation::current()?->activeUsers()->whereKey((int) $id)->exists() ?? false)
            : $modelClass::whereKey((int) $id)->exists();

        if (! $exists) {
            return "Customer {$type} #{$id} does not exist";
        }

        if (mb_strlen(trim((string) $data['title'])) > 255) {
            return 'title may not exceed 255 characters';
        }

        foreach (['subtotal', 'discount_amount', 'tax_amount'] as $column) {
            $value = trim((string) ($data[$column] ?? ''));

            if ($value !== '' && ! preg_match('/^\d{1,8}(\.\d{1,2})?$/', $value)) {
                return "{$column} must be a non-negative amount with up to 2 decimal places";
            }
        }

        $discount = trim((string) ($data['discount_amount'] ?? ''));

        if ($discount !== '' && bccomp($discount, trim((string) $data['subtotal']), 2) === 1) {
            return 'discount_amount cannot exceed subtotal';
        }

        $currency = strtoupper(trim((string) ($data['currency'] ?? '')));

        if ($currency !== '' && ! in_array($currency, config('currencies.allowed'), true)) {
            return "currency '{$currency}' is not permitted";
        }

        foreach (['issued_at', 'valid_until'] as $column) {
            $value = trim((string) ($data[$column] ?? ''));

            if ($value !== '' && ! $this->isValidDate($value)) {
                return "{$column} is not a valid date";
            }
        }

        return null;
    }

    /**
     * Build the attributes to persist from a validated row.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function attributesFromRow(array $data, int $actorId): array
    {
        $amounts = $this->totals->applyDiscount(
            $data['subtotal'],
            $data['discount_amount'] ?? 0,
            $data['tax_amount'],
        );

        $issued = trim((string) ($data['issued_at'] ?? ''));
        $valid = trim((string) ($data['valid_until'] ?? ''));
        $description = trim((string) ($data['description'] ?? ''));
        $notes = trim((string) ($data['notes'] ?? ''));
        $currency = strtoupper(trim((string) ($data['currency'] ?? '')));

        return [
            'quotable_type' => $this->typeRegistry->modelClassForKey(strtolower(trim((string) $data['quotable_type']))),
            'quotable_id' => (int) $data['quotable_id'],
            'title' => trim((string) $data['title']),
            'description' => $description === '' ? null : $description,
            'notes' => $notes === '' ? null : $notes,
            ...$amounts,
            'currency' => $currency === '' ? 'GBP' : $currency,
            'issued_at' => $issued === '' ? null : Carbon::parse($issued),
            'valid_until' => $valid === '' ? null : Carbon::parse($valid)->endOfDay(),
            'status_id' => QuoteStatus::idForKey(QuoteStatus::KEY_DRAFT),
            'created_by' => $actorId,
        ];
    }

    private function isValidDate(string $value): bool
    {
        try {
            Carbon::parse($value);

            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
