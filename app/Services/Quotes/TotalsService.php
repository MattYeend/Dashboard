<?php

declare(strict_types=1);

namespace App\Services\Quotes;

use App\Models\Quote;
use App\Models\QuoteItem;
use Illuminate\Support\Facades\DB;
use LogicException;
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

    /**
     * Calculate a line total: quantity x unit price, less the line discount.
     *
     * All steps use bcmath on strings. The product of a 2dp quantity and a
     * 2dp price is exact at 4dp, so nothing is rounded until the very end,
     * and the final step rounds half up to 2dp.
     */
    public function lineTotal(mixed $quantity, mixed $unitPrice, mixed $discountPercent): string
    {
        $gross = bcmul($this->normalise($quantity), $this->normalise($unitPrice), 4);
        $factor = bcsub('100', $this->normalise($discountPercent), 2);

        return $this->roundHalfUp(bcdiv(bcmul($gross, $factor, 6), '100', 8));
    }

    /**
     * Calculate the tax on a line from its (already rounded) line total.
     */
    public function lineTax(mixed $lineTotal, mixed $taxRate): string
    {
        return $this->roundHalfUp(
            bcdiv(bcmul($this->normalise($lineTotal), $this->normalise($taxRate), 4), '100', 8)
        );
    }

    /**
     * Recalculate the quote header from its lines.
     *
     * The caller MUST hold a transaction and a lockForUpdate() on the quote
     * row, otherwise two simultaneous line changes could overwrite each
     * other's totals. This is enforced rather than assumed.
     *
     * A header discount that is now larger than the subtotal (for example
     * after a line was removed) is reduced to the subtotal.
     *
     * @throws LogicException when called outside a transaction
     */
    public function recalculate(Quote $quote): Quote
    {
        if (DB::transactionLevel() < 1) {
            throw new LogicException('Quote totals must be recalculated inside a transaction.');
        }

        $subtotal = '0.00';
        $tax = '0.00';

        QuoteItem::query()
            ->where('quote_id', $quote->id)
            ->get(['id', 'line_total', 'line_tax'])
            ->each(function (QuoteItem $item) use (&$subtotal, &$tax): void {
                $subtotal = bcadd($subtotal, (string) $item->line_total, 2);
                $tax = bcadd($tax, (string) $item->line_tax, 2);
            });

        $discount = (string) $quote->discount_amount;

        if (bccomp($discount, $subtotal, 2) === 1) {
            $discount = $subtotal;
        }

        $quote->forceFill($this->applyDiscount($subtotal, $discount, $tax))->save();

        return $quote;
    }

    /**
     * Round a non-negative bcmath value half up to two decimal places.
     */
    private function roundHalfUp(string $value): string
    {
        return bcadd($value, '0.005', 2);
    }
}
