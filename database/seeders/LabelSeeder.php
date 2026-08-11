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
                'name' => 'Hot Lead',
                'slug' => 'hot-lead',
                'background_colour' => '#dc2626',
                'text_colour' => '#ffffff',
            ],
            [
                'name' => 'Cold Lead',
                'slug' => 'cold-lead',
                'background_colour' => '#2563eb',
                'text_colour' => '#ffffff',
            ],
            [
                'name' => 'VIP Client',
                'slug' => 'vip-client',
                'background_colour' => '#7c3aed',
                'text_colour' => '#ffffff',
            ],
            [
                'name' => 'Follow Up Required',
                'slug' => 'follow-up-required',
                'background_colour' => '#f59e0b',
                'text_colour' => '#1f2937',
            ],
            [
                'name' => 'Do Not Contact',
                'slug' => 'do-not-contact',
                'background_colour' => '#111827',
                'text_colour' => '#ffffff',
            ],
            [
                'name' => 'Newsletter Subscriber',
                'slug' => 'newsletter-subscriber',
                'background_colour' => '#0891b2',
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
                'name' => 'Referral',
                'slug' => 'referral',
                'background_colour' => '#059669',
                'text_colour' => '#ffffff',
            ],
            [
                'name' => 'Existing Customer',
                'slug' => 'existing-customer',
                'background_colour' => '#16a34a',
                'text_colour' => '#ffffff',
            ],
            [
                'name' => 'Prospect',
                'slug' => 'prospect',
                'background_colour' => '#4f46e5',
                'text_colour' => '#ffffff',
            ],
            [
                'name' => 'Churn Risk',
                'slug' => 'churn-risk',
                'background_colour' => '#ea580c',
                'text_colour' => '#ffffff',
            ],
        ];
    }
}
