<?php

namespace Database\Seeders;

use App\Models\TicketPriority;
use Illuminate\Database\Seeder;

class TicketPrioritySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (TicketPriority::exists()) {
            $this->command->info('Ticket priorities already seeded, skipping...');

            return;
        }

        $priorities = [
            [
                'title' => 'Low',
                'level' => 1,
                'background_colour' => '#e2e8f0',
                'text_colour' => '#1a202c',
                'meta' => null,
            ],
            [
                'title' => 'Medium',
                'level' => 2,
                'background_colour' => '#bee3f8',
                'text_colour' => '#2b6cb0',
                'meta' => null,
            ],
            [
                'title' => 'High',
                'level' => 3,
                'background_colour' => '#feebc8',
                'text_colour' => '#7b341e',
                'meta' => null,
            ],
            [
                'title' => 'Critical',
                'level' => 4,
                'background_colour' => '#fed7d7',
                'text_colour' => '#742a2a',
                'meta' => null,
            ],
        ];

        foreach ($priorities as $priority) {
            TicketPriority::firstOrCreate(
                ['title' => $priority['title']],
                $priority
            );
        }
    }
}
