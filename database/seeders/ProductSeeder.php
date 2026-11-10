<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Product;
use Database\Seeders\Concerns\ResolvesDefaultOrganisation;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    use ResolvesDefaultOrganisation;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $organisation = $this->defaultOrganisation();

        if (Product::withTrashed()->where('organisation_id', $organisation->id)->exists()) {
            $this->command->info('Products already seeded, skipping...');

            return;
        }

        foreach ($this->getProducts() as $product) {
            Product::firstOrCreate(
                [
                    'sku' => $product['sku'],
                    'organisation_id' => $organisation->id,
                ],
                $product
            );
        }
    }

    /**
     * Get the predefined product records to seed.
     *
     * @return array<int, array<string, string|int|float|bool|null>>
     */
    private function getProducts(): array
    {
        return [
            [
                'sku' => 'WEB-DESIGN-DAY',
                'name' => 'Web Design (Day Rate)',
                'description' => 'Senior designer day rate for website and interface design work.',
                'unit' => 'day',
                'unit_price' => 550.00,
                'cost_price' => 320.00,
                'tax_rate' => 20.00,
                'currency' => 'GBP',
                'is_active' => true,
            ],
            [
                'sku' => 'WEB-DEV-HOUR',
                'name' => 'Web Development (Hourly)',
                'description' => 'Laravel and Vue.js development, billed hourly.',
                'unit' => 'hour',
                'unit_price' => 75.00,
                'cost_price' => 42.00,
                'tax_rate' => 20.00,
                'currency' => 'GBP',
                'is_active' => true,
            ],
            [
                'sku' => 'HOSTING-MANAGED-M',
                'name' => 'Managed Hosting (Monthly)',
                'description' => 'Managed cloud hosting with daily backups and monitoring.',
                'unit' => 'month',
                'unit_price' => 95.00,
                'cost_price' => 38.00,
                'tax_rate' => 20.00,
                'currency' => 'GBP',
                'is_active' => true,
            ],
            [
                'sku' => 'SUPPORT-RETAINER-M',
                'name' => 'Support Retainer (Monthly)',
                'description' => 'Ten hours of prioritised support and maintenance each month.',
                'unit' => 'month',
                'unit_price' => 650.00,
                'cost_price' => 420.00,
                'tax_rate' => 20.00,
                'currency' => 'GBP',
                'is_active' => true,
            ],
            [
                'sku' => 'LICENCE-DESIGN-Y',
                'name' => 'Design Tooling Licence (Annual)',
                'description' => 'Annual per-seat licence for design and prototyping software.',
                'unit' => 'licence',
                'unit_price' => 240.00,
                'cost_price' => 190.00,
                'tax_rate' => 20.00,
                'currency' => 'GBP',
                'is_active' => true,
            ],
            [
                'sku' => 'SSL-CERT-Y',
                'name' => 'SSL Certificate (Annual)',
                'description' => 'Extended validation SSL certificate, renewed yearly.',
                'unit' => 'year',
                'unit_price' => 120.00,
                'cost_price' => 70.00,
                'tax_rate' => 20.00,
                'currency' => 'GBP',
                'is_active' => true,
            ],
            [
                'sku' => 'TRAINING-WORKSHOP',
                'name' => 'Team Training Workshop',
                'description' => 'Full-day on-site training workshop for up to ten attendees.',
                'unit' => 'day',
                'unit_price' => 1200.00,
                'cost_price' => 640.00,
                'tax_rate' => 20.00,
                'currency' => 'GBP',
                'is_active' => true,
            ],
            [
                'sku' => 'LEGACY-SEO-AUDIT',
                'name' => 'SEO Audit (Legacy Package)',
                'description' => 'Retired audit package, kept for historic quotes only.',
                'unit' => 'each',
                'unit_price' => 450.00,
                'cost_price' => 200.00,
                'tax_rate' => 20.00,
                'currency' => 'GBP',
                'is_active' => false,
            ],
        ];
    }
}
