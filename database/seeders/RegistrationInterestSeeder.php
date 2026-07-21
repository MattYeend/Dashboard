<?php

namespace Database\Seeders;

use App\Models\RegistrationInterest;
use Illuminate\Database\Seeder;

class RegistrationInterestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (RegistrationInterest::exists()) {
            $this->command->info('Registration interests already seeded, skipping...');

            return;
        }

        foreach ($this->getInterests() as $interest) {
            RegistrationInterest::create($interest);
        }
    }

    /**
     * Get the predefined registration interest records to seed.
     *
     * @return array<int, array<string, string|null>>
     */
    private function getInterests(): array
    {
        return [
            [
                'name' => 'Sarah Kilbride',
                'email' => 's.kilbride@brookfieldlogistics.co.uk',
                'phone' => '01782 445210',
                'company' => 'Brookfield Logistics',
                'message' => 'Interested in the CRM plan for a 12-seat team.',
            ],
            [
                'name' => 'Tom Ainsworth',
                'email' => 'tom@ainsworthjoinery.com',
                'phone' => null,
                'company' => 'Ainsworth Joinery',
                'message' => 'Would like a demo before committing.',
            ],
            [
                'name' => 'Priya Chandra',
                'email' => 'priya.chandra@fieldstonepr.com',
                'phone' => '020 7946 0102',
                'company' => 'Fieldstone PR',
                'message' => null,
            ],
        ];
    }
}
