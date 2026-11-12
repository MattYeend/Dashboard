<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Quote;
use App\Models\QuoteStatus;
use App\Models\User;
use Carbon\CarbonImmutable;
use Database\Seeders\Concerns\ResolvesDefaultOrganisation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class QuoteSeeder extends Seeder
{
    use ResolvesDefaultOrganisation;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Quote::withTrashed()->withoutGlobalScope('organisation')->exists()) {
            $this->command->info('Quotes already seeded, skipping...');

            return;
        }

        $organisation = $this->defaultOrganisation();
        $users = User::all()->keyBy('email');

        if ($users->isEmpty()) {
            $this->command->warn('No users found, skipping quote seeding...');

            return;
        }

        $statuses = QuoteStatus::withoutGlobalScope('organisation')
            ->where('organisation_id', $organisation->id)
            ->whereNotNull('system_key')
            ->pluck('id', 'system_key');

        if ($statuses->isEmpty()) {
            $this->command->warn('No quote statuses found, run QuoteStatusSeeder first...');

            return;
        }

        $morphType = (new User)->getMorphClass();

        foreach ($this->getQuotes($morphType, $users, $statuses) as $quote) {
            Quote::create([...$quote, 'organisation_id' => $organisation->id]);
        }
    }

    /**
     * Get the predefined quote records to seed.
     *
     * Workflow timestamps such as sent_at are system-managed and not mass
     * assignable, so seeded quotes carry only their status.
     *
     * @param  Collection<string, User>  $users
     * @param  Collection<string, int>  $statuses
     * @return array<int, array<string, CarbonImmutable|string|int|float|null>>
     */
    private function getQuotes(string $morphType, Collection $users, Collection $statuses): array
    {
        return [
            [
                'quotable_type' => $morphType,
                'quotable_id' => $users['manager@example.com']->id,
                'quote_number' => 'QUO-100001',
                'title' => 'Website Refresh and Content Migration',
                'description' => 'Visual refresh of the marketing site and migration of existing content to the new CMS.',
                'notes' => 'Customer prefers phased delivery over three months.',
                'terms' => 'Fifty per cent deposit on acceptance, balance on launch.',
                'subtotal' => 6400.00,
                'discount_amount' => 400.00,
                'tax_amount' => 1200.00,
                'total_amount' => 7200.00,
                'currency' => 'GBP',
                'issued_at' => now()->subDays(5),
                'valid_until' => now()->addDays(25),
                'status_id' => $statuses[QuoteStatus::KEY_SENT],
            ],
            [
                'quotable_type' => $morphType,
                'quotable_id' => $users['admin@example.com']->id,
                'quote_number' => 'QUO-100002',
                'title' => 'Annual Managed Hosting and Support',
                'description' => 'Twelve months of managed hosting with a monthly support retainer.',
                'notes' => null,
                'terms' => 'Invoiced quarterly in advance.',
                'subtotal' => 8760.00,
                'discount_amount' => 0.00,
                'tax_amount' => 1752.00,
                'total_amount' => 10512.00,
                'currency' => 'GBP',
                'issued_at' => now()->subDays(12),
                'valid_until' => now()->addDays(18),
                'status_id' => $statuses[QuoteStatus::KEY_ACCEPTED],
            ],
            [
                'quotable_type' => $morphType,
                'quotable_id' => $users['editor@example.com']->id,
                'quote_number' => 'QUO-100003',
                'title' => 'Team Training Workshop Series',
                'description' => 'Three full-day workshops on the new platform for the Edinburgh team.',
                'notes' => 'Draft awaiting confirmation of attendee numbers.',
                'terms' => 'Payment due within 30 days of the final workshop.',
                'subtotal' => 3600.00,
                'discount_amount' => 100.00,
                'tax_amount' => 700.00,
                'total_amount' => 4200.00,
                'currency' => 'GBP',
                'issued_at' => null,
                'valid_until' => now()->addDays(30),
                'status_id' => $statuses[QuoteStatus::KEY_DRAFT],
            ],
            [
                'quotable_type' => $morphType,
                'quotable_id' => $users['john.admin@example.com']->id,
                'quote_number' => 'QUO-100004',
                'title' => 'Legacy System Audit',
                'description' => 'Review of the legacy reporting stack with a written remediation plan.',
                'notes' => 'Customer chose another supplier.',
                'terms' => 'Fixed fee, payable on delivery of the report.',
                'subtotal' => 2400.00,
                'discount_amount' => 0.00,
                'tax_amount' => 480.00,
                'total_amount' => 2880.00,
                'currency' => 'GBP',
                'issued_at' => now()->subMonth(),
                'valid_until' => now()->subDays(10),
                'status_id' => $statuses[QuoteStatus::KEY_DECLINED],
            ],
            [
                'quotable_type' => $morphType,
                'quotable_id' => $users['superadmin@example.com']->id,
                'quote_number' => 'QUO-100005',
                'title' => 'SEO and Analytics Setup',
                'description' => 'Technical SEO review and analytics configuration for the main site.',
                'notes' => 'Left unanswered, expired automatically.',
                'terms' => 'Payment due on completion.',
                'subtotal' => 1500.00,
                'discount_amount' => 0.00,
                'tax_amount' => 300.00,
                'total_amount' => 1800.00,
                'currency' => 'GBP',
                'issued_at' => now()->subMonths(2),
                'valid_until' => now()->subMonth(),
                'status_id' => $statuses[QuoteStatus::KEY_EXPIRED],
            ],
        ];
    }
}
