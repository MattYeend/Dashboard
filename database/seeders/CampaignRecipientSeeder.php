<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\CampaignRecipient;
use Database\Seeders\Concerns\ResolvesDefaultOrganisation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seeds real campaign recipients for the default organisation. No Faker data.
 * Safe to run repeatedly: rows are matched on 'campaign_id' and 'email'.
 */
class CampaignRecipientSeeder extends Seeder
{
    use ResolvesDefaultOrganisation;

    public function run(): void
    {
        $organisation = $this->defaultOrganisation();

        // Each row: ['campaign' => 'Campaign name', 'email' => 'real@address', 'name' => 'Real Name']
        $rows = [];

        foreach ($rows as $row) {
            $campaign = Campaign::where('name', $row['campaign'])->first();

            if ($campaign === null) {
                continue;
            }

            CampaignRecipient::firstOrCreate(
                [
                    'campaign_id' => $campaign->id,
                    'email' => mb_strtolower($row['email']),
                    'organisation_id' => $organisation->id,
                ],
                [
                    'name' => $row['name'] ?? null,
                    'status' => CampaignRecipient::STATUS_PENDING,
                    'token' => Str::random(40),
                    'attempts' => 0,
                ],
            );
        }
    }
}