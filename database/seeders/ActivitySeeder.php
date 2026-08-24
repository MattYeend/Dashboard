<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class ActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Activity::exists()) {
            $this->command->info('Activities already seeded, skipping...');

            return;
        }

        $companies = Company::orderBy('id')->get();

        if ($companies->isEmpty()) {
            $this->command->warn('No companies found, skipping activity seeding...');

            return;
        }

        $morphType = (new Company)->getMorphClass();

        foreach ($this->getActivities($morphType, $companies) as $activity) {
            Activity::create($activity);
        }
    }

    /**
     * Get the predefined activity records to seed, cycling a fixed set of
     * realistic timeline entries across whichever companies exist.
     *
     * @param  Collection<int, Company>  $companies
     * @return array<int, array<string, string|int|null>>
     */
    private function getActivities(string $morphType, Collection $companies): array
    {
        $entries = [
            [
                'type' => 'note',
                'description' => 'Initial discovery call completed, client is interested in the annual plan.',
                'occurred_at_offset_days' => 28,
            ],
            [
                'type' => 'status_change',
                'description' => 'Deal moved from Prospecting to Proposal Sent.',
                'occurred_at_offset_days' => 21,
            ],
            [
                'type' => 'call_logged',
                'description' => 'Follow-up call to discuss contract terms, agreed to revisit pricing next week.',
                'occurred_at_offset_days' => 14,
            ],
            [
                'type' => 'email_logged',
                'description' => 'Sent renewal reminder email ahead of the contract end date.',
                'occurred_at_offset_days' => 7,
            ],
            [
                'type' => 'task_created',
                'description' => 'Created a task to prepare the Q3 account review pack.',
                'occurred_at_offset_days' => 2,
            ],
        ];

        $activities = [];

        foreach ($companies as $company) {
            foreach ($entries as $entry) {
                $activities[] = [
                    'activityable_type' => $morphType,
                    'activityable_id' => $company->id,
                    'type' => $entry['type'],
                    'description' => $entry['description'],
                    'occurred_at' => now()->subDays($entry['occurred_at_offset_days']),
                ];
            }
        }

        return $activities;
    }
}
