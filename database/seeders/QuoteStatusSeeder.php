<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\QuoteStatus;
use Database\Seeders\Concerns\ResolvesDefaultOrganisation;
use Illuminate\Database\Seeder;

class QuoteStatusSeeder extends Seeder
{
    use ResolvesDefaultOrganisation;

    /**
     * Run the database seeds.
     *
     * Keyed on system_key so the seeder can be re-run safely and so a
     * renamed title never produces a duplicate system status.
     */
    public function run(): void
    {
        $organisation = $this->defaultOrganisation();

        foreach ($this->getStatuses() as $status) {
            QuoteStatus::firstOrCreate(
                [
                    'system_key' => $status['system_key'],
                    'organisation_id' => $organisation->id,
                ],
                $status
            );
        }
    }

    /**
     * Get the predefined quote status records to seed.
     *
     * @return array<int, array<string, string|null>>
     */
    private function getStatuses(): array
    {
        return [
            [
                'system_key' => QuoteStatus::KEY_DRAFT,
                'title' => 'Draft',
                'description' => 'Quote is being prepared and has not been sent to the customer.',
                'background_colour' => '#e2e8f0',
                'text_colour' => '#1a202c',
                'meta' => null,
            ],
            [
                'system_key' => QuoteStatus::KEY_SENT,
                'title' => 'Sent',
                'description' => 'Quote has been sent and is awaiting a response from the customer.',
                'background_colour' => '#bee3f8',
                'text_colour' => '#2b6cb0',
                'meta' => null,
            ],
            [
                'system_key' => QuoteStatus::KEY_ACCEPTED,
                'title' => 'Accepted',
                'description' => 'Customer has accepted the quote and it can be converted to an order.',
                'background_colour' => '#c6f6d5',
                'text_colour' => '#22543d',
                'meta' => null,
            ],
            [
                'system_key' => QuoteStatus::KEY_DECLINED,
                'title' => 'Declined',
                'description' => 'Customer has declined the quote.',
                'background_colour' => '#fed7d7',
                'text_colour' => '#742a2a',
                'meta' => null,
            ],
            [
                'system_key' => QuoteStatus::KEY_EXPIRED,
                'title' => 'Expired',
                'description' => 'Quote passed its valid until date without a response.',
                'background_colour' => '#fefcbf',
                'text_colour' => '#744210',
                'meta' => null,
            ],
            [
                'system_key' => QuoteStatus::KEY_CONVERTED,
                'title' => 'Converted',
                'description' => 'The quote has been converted into an order or invoice.',
                'background_colour' => '#a5f3fc',
                'text_colour' => '#164e63',
                'meta' => null,
            ],
        ];
    }
}
