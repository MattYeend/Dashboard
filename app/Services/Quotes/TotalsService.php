<?php

declare(strict_types=1);

namespace App\Services\Quotes;

use InvalidArgumentException;

class TotalsService
{
    /**
     * Normalise a money value to a two-decimal string.
     *
     * @throws InvalidArgumentException
     */
    public function normalise(mixed $value): string
    {
        $value = trim((string) ($value ?? ''));

        if ($value === '') {
            return '0.00';
        }

        if (! preg_match('/^\d+(\.\d+)?$/', $value)) {
            throw new InvalidArgumentException('Money values must be non-negative decimal strings.');
        }

        return bcadd($value, '0', 2);
    }

    /**
     * Apply a header discount and tax to a subtotal.
     *
     * @return array{subtotal: string, discount_amount: string, tax_amount: string, total_amount: string}
     *
     * @throws InvalidArgumentException when the discount exceeds the subtotal
     */
    public function applyDiscount(mixed $subtotal, mixed $discount, mixed $tax): array
    {
        $subtotal = $this->normalise($subtotal);
        $discount = $this->normalise($discount);
        $tax = $this->normalise($tax);

        if (bccomp($discount, $subtotal, 2) === 1) {
            throw new InvalidArgumentException('The discount cannot exceed the subtotal.');
        }

        $net = bcsub($subtotal, $discount, 2);

        return [
            'subtotal' => $subtotal,
            'discount_amount' => $discount,
            'tax_amount' => $tax,
            'total_amount' => bcadd($net, $tax, 2),
        ];
    }
}
