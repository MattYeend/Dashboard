<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Contact;
use App\Models\InteractionLog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class InteractionLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (InteractionLog::exists()) {
            $this->command->info('Interaction logs already seeded, skipping...');

            return;
        }

        $companies = Company::orderBy('id')->get();

        if ($companies->isEmpty()) {
            $this->command->warn('No companies found, skipping interaction log seeding...');

            return;
        }

        $morphType = (new Company)->getMorphClass();

        foreach ($this->getInteractionLogs($morphType, $companies) as $log) {
            InteractionLog::create($log);
        }
    }

    /**
     * Get the predefined interaction log records to seed, cycling a fixed
     * set of realistic entries across whichever companies exist.
     *
     * @param  Collection<int, Company>  $companies
     * @return array<int, array<string, string|int|null>>
     */
    private function getInteractionLogs(string $morphType, Collection $companies): array
    {
        $entries = [
            [
                'type' => 'call',
                'subject' => 'Discovery call',
                'outcome' => 'Client confirmed interest in the annual plan, requested a pricing breakdown.',
                'notes' => 'Spoke with the account owner for around twenty minutes, no blockers raised.',
                'occurred_at_offset_days' => 25,
            ],
            [
                'type' => 'email',
                'subject' => 'Proposal sent',
                'outcome' => 'Proposal document emailed, awaiting response.',
                'notes' => null,
                'occurred_at_offset_days' => 18,
            ],
            [
                'type' => 'call',
                'subject' => 'Contract terms follow-up',
                'outcome' => 'Agreed to revisit pricing the following week.',
                'notes' => 'Client asked about multi-year discount options.',
                'occurred_at_offset_days' => 11,
            ],
            [
                'type' => 'email',
                'subject' => 'Renewal reminder',
                'outcome' => 'Renewal reminder sent ahead of the contract end date.',
                'notes' => null,
                'occurred_at_offset_days' => 5,
            ],
        ];

        $contactIds = $this->getContactIdsByCompany($companies);
        $logs = [];

        foreach ($companies as $company) {
            $contactId = $contactIds[$company->id] ?? null;

            foreach ($entries as $entry) {
                $logs[] = [
                    'interactable_type' => $morphType,
                    'interactable_id' => $company->id,
                    'type' => $entry['type'],
                    'subject' => $entry['subject'],
                    'outcome' => $entry['outcome'],
                    'notes' => $entry['notes'],
                    'occurred_at' => now()->subDays($entry['occurred_at_offset_days']),
                    'contact_id' => $contactId,
                ];
            }
        }

        return $logs;
    }

    /**
     * Map each company to the first contact recorded against it, where one exists.
     *
     * @param  Collection<int, Company>  $companies
     * @return array<int, int>
     */
    private function getContactIdsByCompany(Collection $companies): array
    {
        $companyMorphType = (new Company)->getMorphClass();

        return Contact::whereIn('contactable_id', $companies->pluck('id'))
            ->where('contactable_type', $companyMorphType)
            ->get()
            ->unique('contactable_id')
            ->pluck('id', 'contactable_id')
            ->all();
    }
}
