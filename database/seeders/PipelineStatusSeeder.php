<?php

namespace Database\Seeders;

use App\Models\PipelineStatus;
use Illuminate\Database\Seeder;

class PipelineStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (PipelineStatus::exists()) {
            $this->command->info('Pipeline statuses already seeded, skipping...');

            return;
        }

        $statuses = [
            [
                'title' => 'Open',
                'description' => 'Deal is active and progressing through the pipeline.',
                'background_colour' => '#e2e8f0',
                'text_colour' => '#1a202c',
                'meta' => null,
            ],
            [
                'title' => 'Qualified',
                'description' => 'Lead has been assessed and meets the criteria to proceed.',
                'background_colour' => '#bee3f8',
                'text_colour' => '#2b6cb0',
                'meta' => null,
            ],
            [
                'title' => 'Proposal Sent',
                'description' => 'A proposal or quote has been sent to the prospect.',
                'background_colour' => '#d6bcfa',
                'text_colour' => '#553c9a',
                'meta' => null,
            ],
            [
                'title' => 'Negotiation',
                'description' => 'Terms are being discussed and agreed with the prospect.',
                'background_colour' => '#fefcbf',
                'text_colour' => '#744210',
                'meta' => null,
            ],
            [
                'title' => 'On Hold',
                'description' => 'Deal has been paused pending further information or decisions.',
                'background_colour' => '#feebc8',
                'text_colour' => '#7b341e',
                'meta' => null,
            ],
            [
                'title' => 'Won',
                'description' => 'Deal was successfully closed with the prospect.',
                'background_colour' => '#c6f6d5',
                'text_colour' => '#22543d',
                'meta' => null,
            ],
            [
                'title' => 'Lost',
                'description' => 'Deal did not proceed and has been closed unsuccessfully.',
                'background_colour' => '#fed7d7',
                'text_colour' => '#742a2a',
                'meta' => null,
            ],
            [
                'title' => 'Abandoned',
                'description' => 'Prospect went unresponsive and the deal was withdrawn.',
                'background_colour' => '#e2e8f0',
                'text_colour' => '#718096',
                'meta' => null,
            ],
        ];

        foreach ($statuses as $status) {
            PipelineStatus::firstOrCreate(
                ['title' => $status['title']],
                $status
            );
        }
    }
}
