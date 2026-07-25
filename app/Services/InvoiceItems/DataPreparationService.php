<?php

namespace App\Services\InvoiceItems;

class DataPreparationService
{
    /**
     * Prepare invoice item data for creation.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForCreation(
        array $data,
        int $invoiceItemId,
        int $createdBy
    ): array {
        $quantity = (int) ($data['quantity'] ?? 1);
        $unitPrice = (int) ($data['unit_price'] ?? 0);
        $taxRate = (string) ($data['tax_rate'] ?? '0.00');

        return [
            'invoice_id' => $invoiceItemId,
            'description' => $data['description'],
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'tax_rate' => $taxRate,
            'total' => $this->calculateTotal($quantity, $unitPrice, $taxRate),
            'position' => $data['position'] ?? 0,
            'meta' => $data['meta'] ?? null,
            'created_by' => $createdBy,
        ];
    }

    /**
     * Prepare invoice item data for update.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForUpdate(
        array $data,
        int $updatedBy
    ): array {
        $allowed = [
            'description',
            'quantity',
            'unit_price',
            'tax_rate',
            'position',
            'meta',
        ];

        $payload = [];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $payload[$field] = $data[$field];
            }
        }

        if (array_key_exists('quantity', $payload) || array_key_exists('unit_price', $payload) || array_key_exists('tax_rate', $payload)) {
            $payload['total'] = $this->calculateTotal(
                (int) ($payload['quantity'] ?? $data['current_quantity']),
                (int) ($payload['unit_price'] ?? $data['current_unit_price']),
                (string) ($payload['tax_rate'] ?? $data['current_tax_rate'])
            );
        }

        $payload['updated_by'] = $updatedBy;

        return $payload;
    }

    /**
     * Calculate the line total (unit price × quantity, plus tax) in minor units.
     */
    private function calculateTotal(
        int $quantity,
        int $unitPrice,
        string $taxRate
    ): int {
        $net = $quantity * $unitPrice;

        return (int) round($net * (1 + ((float) $taxRate / 100)));
    }
}
