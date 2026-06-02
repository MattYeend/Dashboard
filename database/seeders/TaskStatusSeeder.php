<?php

namespace Database\Seeders;

use App\Models\TaskStatus;
use Illuminate\Database\Seeder;

class TaskStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (TaskStatus::exists()) {
            $this->command->info('Task statuses already seeded, skipping...');

            return;
        }

        $statuses = [
            [
                'title' => 'To Do',
                'description' => 'Task has been created but work has not yet started.',
                'background_colour' => '#e2e8f0',
                'text_colour' => '#1a202c',
                'meta' => null,
            ],
            [
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

        foreach ($statuses as $status) {
            TaskStatus::firstOrCreate(
                ['title' => $status['title']],
                $status
            );
        }
    }
}
