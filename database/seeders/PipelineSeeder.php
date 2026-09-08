<?php

namespace Database\Seeders;

use App\Models\Pipeline;
use App\Models\PipelineStatus;
use App\Models\User;
use Database\Seeders\Concerns\ResolvesDefaultOrganisation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class PipelineSeeder extends Seeder
{
    use ResolvesDefaultOrganisation;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Pipeline::withTrashed()->withoutGlobalScope('organisation')->exists()) {
            $this->command->info('Pipelines already seeded, skipping...');

            return;
        }

        $organisation = $this->defaultOrganisation();

        $statuses = PipelineStatus::withoutGlobalScope('organisation')
            ->where('organisation_id', $organisation->id)
            ->get()->keyBy('title');

        if ($statuses->isEmpty()) {
            $this->command->warn('No pipeline statuses found, pipelines will be seeded without a status...');
        }

        $users = User::orderBy('id')->get();

        if ($users->isEmpty()) {
            $this->command->warn('No users found, skipping pipeline seeding...');

            return;
        }

        $creator = $users->first();

        foreach ($this->getPipelines($statuses, $users) as $pipeline) {
            Pipeline::create([
                ...$pipeline,
                'organisation_id' => $organisation->id,
                'created_by' => $creator->id,
            ]);
        }
    }

    /**
     * Get the predefined pipeline records to seed.
     *
     * @param  Collection<string, PipelineStatus>  $statuses
     * @param  Collection<int, User>  $users
     * @return array<int, array<string, string|bool|int|null>>
     */
    private function getPipelines($statuses, $users): array
    {
        return [
            [
                'title' => 'Sales Pipeline',
                'description' => 'Tracks prospective deals from initial enquiry through to close.',
                'is_default' => true,
                'status_id' => $statuses->get('Open')?->id,
                'assigned_to' => $users->first()?->id,
            ],
            [
                'title' => 'Recruitment Pipeline',
                'description' => 'Tracks candidates from application through to offer.',
                'is_default' => false,
                'status_id' => $statuses->get('Open')?->id,
                'assigned_to' => $users->get(1)?->id ?? $users->first()?->id,
            ],
            [
                'title' => 'Support Pipeline',
                'description' => 'Tracks support tickets from raised through to resolved.',
                'is_default' => false,
                'status_id' => $statuses->get('Open')?->id,
                'assigned_to' => $users->get(2)?->id ?? $users->first()?->id,
            ],
        ];
    }
}
