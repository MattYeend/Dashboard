<?php

namespace Database\Seeders;

use App\Models\TicketStatus;
use Illuminate\Database\Seeder;

class TicketStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (TicketStatus::exists()) {
            $this->command->info('Ticket statuses already seeded, skipping...');

            return;
        }

        $statuses = [
            [
                'title' => 'Open',
                'background_colour' => '#e2e8f0',
                'text_colour' => '#1a202c',
                'meta' => null,
            ],
            [
                'title' => 'In Progress',
                'background_colour' => '#bee3f8',
                'text_colour' => '#2b6cb0',
                'meta' => null,
            ],
            [
                'title' => 'On Hold',
                'background_colour' => '#feebc8',
                'text_colour' => '#7b341e',
                'meta' => null,
            ],
            [
                'title' => 'Pending Customer',
                'background_colour' => '#fefcbf',
                'text_colour' => '#744210',
                'meta' => null,
            ],
            [
                'title' => 'Resolved',
                'background_colour' => '#c6f6d5',
                'text_colour' => '#22543d',
                'meta' => null,
            ],
            [
                'title' => 'Closed',
                'background_colour' => '#e2e8f0',
                'text_colour' => '#718096',
                'meta' => null,
            ],
            [
                'title' => 'Reopened',
                'background_colour' => '#fed7d7',
                'text_colour' => '#742a2a',
                'meta' => null,
            ],
        ];

        foreach ($statuses as $status) {
            TicketStatus::firstOrCreate(
                ['title' => $status['title']],
                $status
            );
        }
    }
}
