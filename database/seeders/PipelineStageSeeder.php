<?php

namespace Database\Seeders;

use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\User;
use Illuminate\Database\Seeder;

class PipelineStageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (PipelineStage::exists()) {
            $this->command->info('Pipeline stages already seeded, skipping...');

            return;
        }

        $pipelines = Pipeline::all();

        if ($pipelines->isEmpty()) {
            $this->command->warn('No pipelines found, skipping pipeline stage seeding...');

            return;
        }

        $creator = User::first();

        if ($creator === null) {
            $this->command->warn('No users found, skipping pipeline stage seeding...');

            return;
        }

        $stages = $this->getStages();

        foreach ($pipelines as $pipeline) {
            foreach ($stages as $position => $stage) {
                PipelineStage::updateOrCreate(
                    [
                        'pipeline_id' => $pipeline->id,
                        'title' => $stage['title'],
                    ],
                    [
                        'description' => $stage['description'],
                        'position' => $position,
                        'background_colour' => $stage['background_colour'],
                        'text_colour' => $stage['text_colour'],
                        'is_won' => $stage['is_won'],
                        'is_lost' => $stage['is_lost'],
                        'created_by' => $creator->id,
                    ]
                );
            }
        }
    }

    /**
     * Get the standard real pipeline stage progression applied to every pipeline.
     *
     * @return array<int, array<string, string|bool>>
     */
    private function getStages(): array
    {
        return [
            [
                'title' => 'Lead',
                'description' => 'A new prospect has been identified but not yet contacted.',
                'background_colour' => '#e0e7ff',
                'text_colour' => '#3730a3',
                'is_won' => false,
                'is_lost' => false,
            ],
            [
                'title' => 'Contacted',
                'description' => 'Initial contact has been made with the prospect.',
                'background_colour' => '#dbeafe',
                'text_colour' => '#1e40af',
                'is_won' => false,
                'is_lost' => false,
            ],
            [
                'title' => 'Qualified',
                'description' => 'The prospect has been assessed and meets the criteria to proceed.',
                'background_colour' => '#cffafe',
                'text_colour' => '#155e75',
                'is_won' => false,
                'is_lost' => false,
            ],
            [
                'title' => 'Proposal Sent',
                'description' => 'A formal proposal or quote has been sent to the prospect.',
                'background_colour' => '#fef9c3',
                'text_colour' => '#854d0e',
                'is_won' => false,
                'is_lost' => false,
            ],
            [
                'title' => 'Negotiation',
                'description' => 'Terms are being discussed and negotiated with the prospect.',
                'background_colour' => '#fed7aa',
                'text_colour' => '#9a3412',
                'is_won' => false,
                'is_lost' => false,
            ],
            [
                'title' => 'Won',
                'description' => 'The deal has been closed successfully.',
                'background_colour' => '#bbf7d0',
                'text_colour' => '#166534',
                'is_won' => true,
                'is_lost' => false,
            ],
            [
                'title' => 'Lost',
                'description' => 'The deal did not proceed and has been closed unsuccessfully.',
                'background_colour' => '#fecaca',
                'text_colour' => '#991b1b',
                'is_won' => false,
                'is_lost' => true,
            ],
        ];
    }
}
