<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\CampaignStatus;
use Database\Seeders\Concerns\ResolvesDefaultOrganisation;
use Illuminate\Database\Seeder;

/**
 * Seeds real campaigns for the default organisation. No Faker data.
 * Safe to run repeatedly: rows are matched on name.
 */
class CampaignSeeder extends Seeder
{
    use ResolvesDefaultOrganisation;

    public function run(): void
    {
        $organisation = $this->defaultOrganisation();

        $rows = [
            [
                'name' => 'Welcome Series Launch',
                'from_name' => 'Dashboard Team',
                'status_id' => CampaignStatus::idForKey(
                    CampaignStatus::KEY_DRAFT,
                ),
            ],
        ];

        foreach ($rows as $row) {
            Campaign::firstOrCreate(
                [
                    'name' => $row['name'],
                    'organisation_id' => $organisation->id,
                ],
                $row,
            );
        }
    }
}

