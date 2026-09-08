<?php

namespace Database\Seeders;

use App\Models\TicketStatus;
use Database\Seeders\Concerns\ResolvesDefaultOrganisation;
use Illuminate\Database\Seeder;

class TicketStatusSeeder extends Seeder
{
    use ResolvesDefaultOrganisation;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $organisation = $this->defaultOrganisation();

        if (TicketStatus::withoutGlobalScope('organisation')->where('organisation_id', $organisation->id)->exists()) {
            $this->command->info('Ticket statuses already seeded, skipping...');

            return;
        }

        $statuses = [
            [
                'title' => 'Open',
                'description' => 'The ticket has been created and is awaiting action.',
                'background_colour' => '#e2e8f0',
                'text_colour' => '#1a202c',
                'meta' => null,
            ],
            [
                'title' => 'In Progress',
                'description' => 'The ticket is currently being worked on.',
                'background_colour' => '#bee3f8',
                'text_colour' => '#2b6cb0',
                'meta' => null,
            ],
            [
                'title' => 'On Hold',
                'description' => 'The ticket is temporarily paused and awaiting further action.',
                'background_colour' => '#feebc8',
                'text_colour' => '#7b341e',
                'meta' => null,
            ],
            [
                'title' => 'Pending Customer',
                'description' => 'The ticket is awaiting a response or action from the customer.',
                'background_colour' => '#fefcbf',
                'text_colour' => '#744210',
                'meta' => null,
            ],
            [
                'title' => 'Resolved',
                'description' => 'The ticket has been resolved and is awaiting confirmation from the customer.',
                'background_colour' => '#c6f6d5',
                'text_colour' => '#22543d',
                'meta' => null,
            ],
            [
                'title' => 'Closed',
                'description' => 'The ticket has been closed and no further action is required.',
                'background_colour' => '#e2e8f0',
                'text_colour' => '#718096',
                'meta' => null,
            ],
            [
                'title' => 'Reopened',
                'description' => 'The ticket has been reopened after being closed.',
                'background_colour' => '#fed7d7',
                'text_colour' => '#742a2a',
                'meta' => null,
            ],
        ];

        foreach ($statuses as $status) {
            TicketStatus::withoutGlobalScope('organisation')->firstOrCreate(
                [
                    'title' => $status['title'],
                    'organisation_id' => $organisation->id,
                ],
                $status
            );
        }
    }
}
