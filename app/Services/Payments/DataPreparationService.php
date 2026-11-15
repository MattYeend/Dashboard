<?php

declare(strict_types=1);

namespace App\Services\Payments;

use Illuminate\Support\Carbon;
use InvalidArgumentException;

class DataPreparationService
{
    /**
     * Prepare payment data for creation.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForCreation(array $data): array
    {
        return [
            'invoice_id' => (int) $data['invoice_id'],
            'method' => (string) $data['method'],
            'amount' => $this->normaliseAmount($data['amount']),
            'paid_at' => Carbon::parse((string) $data['paid_at']),
            'reference' => $this->nullableText($data['reference'] ?? null),
            'notes' => $this->nullableText($data['notes'] ?? null),
        ];
    }

    /**
     * Prepare payment data for update. Amount and invoice are never
     * editable.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForUpdate(array $data): array
    {
        $payload = [];

        foreach (['method', 'paid_at', 'reference', 'notes'] as $field) {
            if (! array_key_exists($field, $data)) {
                continue;
            }

            $payload[$field] = match ($field) {
                'method' => (string) $data[$field],
                'paid_at' => Carbon::parse((string) $data[$field]),
                default => $this->nullableText($data[$field]),
            };
        }

        return $payload;
    }

    /**
     * Prepare refund data.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForRefund(array $data): array
    {
        return [
            'method' => (string) $data['method'],
            'amount' => $this->normaliseAmount($data['amount']),
            'paid_at' => Carbon::parse((string) $data['paid_at']),
            'reference' => $this->nullableText($data['reference'] ?? null),
            'notes' => $this->nullableText($data['notes'] ?? null),
        ];
    }

    /**
     * Normalise an amount to a two-decimal string without ever using a
     * float.
     *
     * @throws InvalidArgumentException
     */
    public function normaliseAmount(mixed $value): string
    {
        $value = trim((string) $value);

        if (! preg_match('/^\d{1,8}(\.\d{1,2})?$/', $value)) {
            throw new InvalidArgumentException(
                'Amounts must be positive decimal values with at most two decimal places.'
            );
        }

        return bcadd($value, '0', 2);
    }

    /**
     * Trim free text and turn an empty string into null. The text is stored
     * as plain text and only ever rendered with Vue interpolation, never
     * with v-html.
     */
    protected function nullableText(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }
}
