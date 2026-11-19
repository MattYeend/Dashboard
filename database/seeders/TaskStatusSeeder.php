<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\TaskStatus;
use Database\Seeders\Concerns\ResolvesDefaultOrganisation;
use Database\Seeders\Concerns\SeedsSystemStatuses;
use Illuminate\Database\Seeder;

class TaskStatusSeeder extends Seeder
{
    use ResolvesDefaultOrganisation,
        SeedsSystemStatuses;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $organisation = $this->defaultOrganisation();

        $statuses = [
            [
                'system_key' => TaskStatus::KEY_TODO,
                'title' => 'To Do',
                'description' => 'Task has been created but work has not yet started.',
                'background_colour' => '#e2e8f0',
                'text_colour' => '#1a202c',
                'meta' => null,
            ],
            [
                'system_key' => TaskStatus::KEY_IN_PROGRESS,
                'title' => 'In Progress',
                'description' => 'Task is actively being worked on.',
                'background_colour' => '#bee3f8',
                'text_colour' => '#2b6cb0',
                'meta' => null,
            ],
            [
                'title' => 'In Review',
                'description' => 'Task is complete and awaiting review or approval.',
                'background_colour' => '#fefcbf',
                'text_colour' => '#744210',
                'meta' => null,
            ],
            [
                'title' => 'Blocked',
                'description' => 'Task cannot proceed due to a dependency or issue.',
                'background_colour' => '#fed7d7',
                'text_colour' => '#742a2a',
                'meta' => null,
            ],
            [
                'system_key' => TaskStatus::KEY_DONE,
                'title' => 'Done',
                'description' => 'Task has been completed and signed off.',
                'background_colour' => '#c6f6d5',
                'text_colour' => '#22543d',
                'meta' => null,
            ],
            [
                'title' => 'Cancelled',
                'description' => 'Task has been cancelled and will not be completed.',
                'background_colour' => '#e2e8f0',
                'text_colour' => '#718096',
                'meta' => null,
            ],
        ];

        $this->seedSystemStatuses(TaskStatus::class, (int) $organisation->id, $statuses);
    }
}
