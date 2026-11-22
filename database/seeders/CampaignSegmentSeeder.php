<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CampaignSegment;
use App\Models\Organisation;
use Illuminate\Database\Seeder;

/**
 * Seeds real campaign segments for the default organisation. No Faker data.
 * Safe to run repeatedly: rows are matched on name within the organisation.
 */
class CampaignSegmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $organisation = Organisation::query()->oldest('id')->firstOrFail();
        $organisation->makeCurrent();

        $rows = [
            [
                'name' => 'All Subscribed Contacts',
                'description' => 'Every contact who has opted in to marketing email.',
                'criteria' => [
                    ['field' => 'email_subscribed', 'operator' => 'equals', 'value' => true],
                ],
            ],
            [
                'name' => 'New Customers (Last 30 Days)',
                'description' => 'Subscribed contacts created within the last 30 days.',
                'criteria' => [
                    ['field' => 'email_subscribed', 'operator' => 'equals', 'value' => true],
                    ['field' => 'created_within_days', 'operator' => 'equals', 'value' => 30],
                ],
            ],
            [
                'name' => 'Inactive Customers (90 Days)',
                'description' => 'Subscribed contacts with no activity in the last 90 days.',
                'criteria' => [
                    ['field' => 'email_subscribed', 'operator' => 'equals', 'value' => true],
                    ['field' => 'inactive_days', 'operator' => 'greater_than', 'value' => 90],
                ],
            ],
        ];

        foreach ($rows as $row) {
            $exists = CampaignSegment::query()
                ->where('organisation_id', $organisation->id)
                ->where('name', $row['name'])
                ->exists();

            if ($exists) {
                continue;
            }

            $segment = new CampaignSegment($row);
            $segment->forceFill(['organisation_id' => $organisation->id])->save();
        }
    }
}
