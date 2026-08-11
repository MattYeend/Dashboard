<?php

namespace Database\Seeders;

use App\Models\Label;
use Illuminate\Database\Seeder;

class LabelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Label::exists()) {
            $this->command->info('Labels already seeded, skipping...');

            return;
        }

        foreach ($this->getLabels() as $label) {
            Label::create($label);
        }
    }

    /**
     * Get the predefined label records to seed.
     *
     * @return array<int, array<string, string|null>>
     */
    private function getLabels(): array
    {
        return [
            [
                'name' => 'VIP',
                'slug' => 'vip',
                'background_colour' => '#7c3aed',
                'text_colour' => '#ffffff',
            ],
            [
                'name' => 'High Priority',
                'slug' => 'high-priority',
                'background_colour' => '#b91c1c',
                'text_colour' => '#ffffff',
            ],
            [
                'name' => 'Low Priority',
                'slug' => 'low-priority',
                'background_colour' => '#9ca3af',
                'text_colour' => '#1f2937',
            ],
            [
                'name' => 'Urgent',
                'slug' => 'urgent',
                'background_colour' => '#dc2626',
                'text_colour' => '#ffffff',
            ],
            [
                'name' => 'Needs Review',
                'slug' => 'needs-review',
                'background_colour' => '#f59e0b',
                'text_colour' => '#1f2937',
            ],
            [
                'name' => 'On Hold',
                'slug' => 'on-hold',
                'background_colour' => '#6b7280',
                'text_colour' => '#ffffff',
            ],
            [
                'name' => 'Do Not Contact',
                'slug' => 'do-not-contact',
                'background_colour' => '#111827',
                'text_colour' => '#ffffff',
            ],
            [
                'name' => 'Internal',
                'slug' => 'internal',
                'background_colour' => '#0891b2',
                'text_colour' => '#ffffff',
            ],
            [
                'name' => 'External Partner',
                'slug' => 'external-partner',
                'background_colour' => '#4f46e5',
                'text_colour' => '#ffffff',
            ],
            [
                'name' => 'Flagged',
                'slug' => 'flagged',
                'background_colour' => '#ea580c',
                'text_colour' => '#ffffff',
            ],
            [
                'name' => 'Archived',
                'slug' => 'archived',
                'background_colour' => '#57534e',
                'text_colour' => '#ffffff',
            ],
            [
                'name' => 'Requires Follow Up',
                'slug' => 'requires-follow-up',
                'background_colour' => '#16a34a',
                'text_colour' => '#ffffff',
            ],
        ];
    }
}
