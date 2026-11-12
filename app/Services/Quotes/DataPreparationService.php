<?php

declare(strict_types=1);

namespace App\Services\Quotes;

use App\Models\Quote;
use App\Models\QuoteStatus;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

class DataPreparationService
{
    public function __construct(
        private readonly QuotableTypeRegistryService $registry,
        private readonly TotalsService $totals,
    ) {}

    /**
     * Prepare quote data for creation.
     *
     * The status is always Draft and the total is always calculated here.
     * Any status_id or total_amount in $data is ignored.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForCreation(array $data): array
    {
        $amounts = $this->totals->applyDiscount(
            $data['subtotal'] ?? 0,
            $data['discount_amount'] ?? 0,
            $data['tax_amount'] ?? 0,
        );

        return [
            'quotable_type' => $this->resolveQuotableType((string) $data['quotable_type']),
            'quotable_id' => (int) $data['quotable_id'],
            'title' => trim((string) $data['title']),
            'description' => $data['description'] ?? null,
            'notes' => $data['notes'] ?? null,
            'terms' => $data['terms'] ?? null,
            ...$amounts,
            'currency' => $data['currency'] ?? 'GBP',
            'issued_at' => $this->parseDate($data['issued_at'] ?? null),
            'valid_until' => $this->parseEndOfDay($data['valid_until'] ?? null),
            'status_id' => QuoteStatus::idForKey(QuoteStatus::KEY_DRAFT),
            'meta' => $data['meta'] ?? null,
        ];
    }

    /**
     * Prepare quote data for update. Only allow-listed fields are copied,
     * and totals are recalculated from the merged values.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForUpdate(array $data, Quote $quote): array
    {
        $allowed = [
            'quotable_type',
            'quotable_id',
            'title',
            'description',
            'notes',
            'terms',
            'currency',
            'meta',
        ];

        $payload = [];

        foreach ($allowed as $field) {
            if (! array_key_exists($field, $data)) {
                continue;
            }

            $payload[$field] = match ($field) {
                'quotable_type' => $this->resolveQuotableType((string) $data[$field]),
                'title' => trim((string) $data[$field]),
                default => $data[$field],
            };
        }

        if (array_key_exists('issued_at', $data)) {
            $payload['issued_at'] = $this->parseDate($data['issued_at']);
        }

        if (array_key_exists('valid_until', $data)) {
            $payload['valid_until'] = $this->parseEndOfDay($data['valid_until']);
        }

        if (array_intersect(['subtotal', 'discount_amount', 'tax_amount'], array_keys($data)) !== []) {
            $payload += $this->totals->applyDiscount(
                $data['subtotal'] ?? $quote->subtotal,
                $data['discount_amount'] ?? $quote->discount_amount,
                $data['tax_amount'] ?? $quote->tax_amount,
            );
        }

        return $payload;
    }

    /**
     * Convert the short key (e.g. "company") into the FQCN stored on the row.
     *
     * @throws InvalidArgumentException
     */
    private function resolveQuotableType(string $quotableType): string
    {
        return $this->registry->modelClassForKey($quotableType)
            ?? throw new InvalidArgumentException("Unrecognised quotable type: {$quotableType}");
    }

    private function parseDate(mixed $value): ?Carbon
    {
        return $value === null || $value === '' ? null : Carbon::parse((string) $value);
    }

    /**
     * A "valid until" date means the whole of that day.
     */
    private function parseEndOfDay(mixed $value): ?Carbon
    {
        return $value === null || $value === '' ? null : Carbon::parse((string) $value)->endOfDay();
    }
}
