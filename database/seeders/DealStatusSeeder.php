<?php

namespace Database\Seeders;

use App\Models\DealStatus;
use Illuminate\Database\Seeder;

class DealStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (DealStatus::exists()) {
            $this->command->info('Deal statuses already seeded, skipping...');

            return;
        }

        $statuses = [
            [
                'title' => 'New',
                'description' => 'Deal has been created and has not yet been reviewed.',
                'background_colour' => '#e2e8f0',
                'text_colour' => '#1a202c',
                'is_closing' => false,
                'meta' => null,
            ],
            [
                'title' => 'Qualified',
                'description' => 'Deal has been reviewed and meets the criteria to be pursued.',
                'background_colour' => '#bee3f8',
                'text_colour' => '#2b6cb0',
                'is_closing' => false,
                'meta' => null,
            ],
            [
                'title' => 'Proposal Sent',
                'description' => 'A proposal or quote has been sent to the client.',
                'background_colour' => '#d6bcfa',
                'text_colour' => '#553c9a',
                'is_closing' => false,
                'meta' => null,
            ],
            [
                'title' => 'Negotiation',
                'description' => 'Terms are being discussed and negotiated with the client.',
                'background_colour' => '#fefcbf',
                'text_colour' => '#744210',
                'is_closing' => false,
                'meta' => null,
            ],
            [
                'title' => 'Won',
                'description' => 'Deal has been agreed and closed successfully.',
                'background_colour' => '#c6f6d5',
                'text_colour' => '#22543d',
                'is_closing' => true,
                'meta' => null,
            ],
            [
                'title' => 'Lost',
                'description' => 'Deal did not proceed and has been closed unsuccessfully.',
                'background_colour' => '#fed7d7',
                'text_colour' => '#742a2a',
                'is_closing' => true,
                'meta' => null,
            ],
            [
                'title' => 'On Hold',
                'description' => 'Deal has been paused pending further information.',
                'background_colour' => '#feb2b2',
                'text_colour' => '#822727',
                'is_closing' => false,
                'meta' => null,
            ],
        ];

        foreach ($statuses as $status) {
            DealStatus::firstOrCreate(
                ['title' => $status['title']],
                $status
            );
        }

        // Ensure closing flags are correct even if rows already existed.
        DealStatus::updateOrCreate(['title' => 'Won'], ['is_closing' => true]);
        DealStatus::updateOrCreate(['title' => 'Lost'], ['is_closing' => true]);
        DealStatus::updateOrCreate(['title' => 'Open'], ['is_closing' => false]);
    }
}
