<?php

namespace Database\Seeders;

use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReportSeeder extends Seeder
{
    /**
     * Seed the reports table.
     */
    public function run(): void
    {
        if (Report::exists()) {
            $this->command->info('Reports already seeded, skipping...');

            return;
        }

        $creator = User::first();

        if ($creator === null) {
            $this->command->warn('No users found, skipping report seeding...');

            return;
        }

        Report::create([
            'title' => 'Monthly Orders Summary',
            'description' => 'Total orders and revenue broken down by status for the past calendar month.',
            'type' => 'orders',
            'format' => 'pdf',
            'filters' => ['period' => 'last_month'],
            'is_scheduled' => true,
            'schedule_frequency' => 'monthly',
            'schedule_time' => '06:00',
            'recipients' => ['ops@example.com'],
            'created_by' => $creator->id,
        ]);

        Report::create([
            'title' => 'Weekly New Companies',
            'description' => 'Companies added to the CRM in the previous seven days.',
            'type' => 'companies',
            'format' => 'csv',
            'filters' => ['period' => 'last_7_days'],
            'is_scheduled' => true,
            'schedule_frequency' => 'weekly',
            'schedule_time' => '08:00',
            'recipients' => ['sales@example.com'],
            'created_by' => $creator->id,
        ]);

        Report::create([
            'title' => 'User Activity Export',
            'description' => 'One-off export of active users for the compliance review.',
            'type' => 'users',
            'format' => 'xlsx',
            'filters' => [],
            'is_scheduled' => false,
            'created_by' => $creator->id,
        ]);
    }
}
