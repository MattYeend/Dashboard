<?php

declare(strict_types=1);

namespace Tests\Concerns;

use App\Models\Company;
use App\Models\Quote;
use App\Models\QuoteStatus;

trait CreatesQuotes
{
    public Quote $quote;

    public function draftQuote(): Quote
    {
        return Quote::factory()
            ->forModel(Company::factory()->create())
            ->withStatus(QuoteStatus::KEY_DRAFT)
            ->create([
                'subtotal' => 0,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'total_amount' => 0,
            ]);
    }
}
