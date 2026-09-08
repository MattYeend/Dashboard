<?php

namespace App\Services;

use NumberFormatter;

class CurrencyFormatterService
{
    /** Format a major-units float amount as a currency string for the given ISO 4217 code. */
    public function format(
        float $amount,
        string $currency
    ): string {
        $formatter = new NumberFormatter('en_GB', NumberFormatter::CURRENCY);

        return $formatter->formatCurrency($amount, strtoupper($currency));
    }
}
