<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Services\Quotes\TotalsService;
use Database\Seeders\Concerns\ResolvesDefaultOrganisation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuoteItemSeeder extends Seeder
{
    use ResolvesDefaultOrganisation;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (QuoteItem::withTrashed()->withoutGlobalScope('organisation')->exists()) {
            $this->command->info('Quote items already seeded, skipping...');

            return;
        }

        $organisation = $this->defaultOrganisation();
        $totals = app(TotalsService::class);

        $products = Product::withoutGlobalScope('organisation')
            ->where('organisation_id', $organisation->id)
            ->get()
            ->keyBy('sku');

        if ($products->isEmpty()) {
            $this->command->warn('No products found, run ProductSeeder first...');

            return;
        }

        foreach ($this->getLines() as $quoteNumber => $lines) {
            $quote = Quote::withoutGlobalScope('organisation')
                ->where('organisation_id', $organisation->id)
                ->where('quote_number', $quoteNumber)
                ->first();

            if ($quote === null) {
                $this->command->warn("Quote {$quoteNumber} not found, skipping its lines...");

                continue;
            }

            DB::transaction(function () use ($lines, $quote, $products, $totals, $organisation): void {
                foreach ($lines as $index => $line) {
                    $product = $products[$line['sku']];
                    $lineTotal = $totals->lineTotal($line['quantity'], $product->unit_price, $line['discount_percent']);

                    QuoteItem::create([
                        'organisation_id' => $organisation->id,
                        'quote_id' => $quote->id,
                        'product_id' => $product->id,
                        'position' => $index + 1,
                        'name' => $product->name,
                        'description' => $line['description'],
                        'unit' => $product->unit,
                        'quantity' => $line['quantity'],
                        'unit_price' => $product->unit_price,
                        'discount_percent' => $line['discount_percent'],
                        'tax_rate' => $product->tax_rate,
                        'line_total' => $lineTotal,
                        'line_tax' => $totals->lineTax($lineTotal, $product->tax_rate),
                    ]);
                }

                $totals->recalculate($quote->refresh());
            });
        }
    }

    /**
     * Get the predefined lines, keyed by quote number.
     *
     * @return array<string, array<int, array<string, string>>>
     */
    private function getLines(): array
    {
        return [
            'QUO-100001' => [
                ['sku' => 'WEB-DESIGN-DAY', 'quantity' => '6.00', 'discount_percent' => '5.00', 'description' => 'Design of the new homepage, landing pages and templates.'],
                ['sku' => 'WEB-DEV-HOUR', 'quantity' => '40.00', 'discount_percent' => '0.00', 'description' => 'CMS build and content migration.'],
                ['sku' => 'SSL-CERT-Y', 'quantity' => '1.00', 'discount_percent' => '0.00', 'description' => 'Extended validation certificate for the live site.'],
            ],
            'QUO-100002' => [
                ['sku' => 'HOSTING-MANAGED-M', 'quantity' => '12.00', 'discount_percent' => '0.00', 'description' => 'Twelve months of managed hosting.'],
                ['sku' => 'SUPPORT-RETAINER-M', 'quantity' => '12.00', 'discount_percent' => '0.00', 'description' => 'Monthly support retainer, ten hours each month.'],
            ],
            'QUO-100003' => [
                ['sku' => 'TRAINING-WORKSHOP', 'quantity' => '3.00', 'discount_percent' => '2.50', 'description' => 'Three full-day workshops for the Edinburgh team.'],
            ],
            'QUO-100004' => [
                ['sku' => 'WEB-DEV-HOUR', 'quantity' => '32.00', 'discount_percent' => '0.00', 'description' => 'Audit of the legacy reporting stack and written findings.'],
            ],
            'QUO-100005' => [
                ['sku' => 'WEB-DEV-HOUR', 'quantity' => '20.00', 'discount_percent' => '0.00', 'description' => 'Analytics configuration and tagging.'],
                ['sku' => 'LEGACY-SEO-AUDIT', 'quantity' => '1.00', 'discount_percent' => '0.00', 'description' => 'Technical SEO review.'],
            ],
        ];
    }
}
