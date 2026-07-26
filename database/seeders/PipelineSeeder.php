<?php

namespace Database\Seeders;

use App\Models\Pipeline;
use App\Models\PipelineStatus;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class PipelineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Pipeline::exists()) {
            $this->command->info('Pipelines already seeded, skipping...');

            return;
        }

        $statuses = PipelineStatus::all()->keyBy('title');

        if ($statuses->isEmpty()) {
            $this->command->warn('No pipeline statuses found, pipelines will be seeded without a status...');
        }

        $creator = User::orderBy('id')->first();

        if ($creator === null) {
            $this->command->warn('No users found, skipping pipeline seeding...');

            return;
        }

        foreach ($this->getPipelines($statuses) as $pipeline) {
            Pipeline::create([
                ...$pipeline,
                'created_by' => $creator->id,
            ]);
        }
    }

    /**
     * Get the predefined pipeline records to seed.
     *
     * @param  Collection<string, PipelineStatus>  $statuses
     * @return array<int, array<string, string|bool|int|null>>
     */
    private function getPipelines($statuses): array
    {
        return [
            [
                'title' => 'Sales Pipeline',
                'description' => 'Tracks prospective deals from initial enquiry through to close.',
                'is_default' => true,
                'status_id' => $statuses->get('Open')?->id,
            ],
            [
                'title' => 'Recruitment Pipeline',
                'description' => 'Tracks candidates from application through to offer.',
                'is_default' => false,
                'status_id' => $statuses->get('Open')?->id,
            ],
            [
                'title' => 'Support Pipeline',
                'description' => 'Tracks support tickets from raised through to resolved.',
                'is_default' => false,
                'status_id' => $statuses->get('Open')?->id,
            ],
        ];
    }
}