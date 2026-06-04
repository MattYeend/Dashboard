<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\User;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Task::exists()) {
            $this->command->info('Tasks already seeded, skipping...');

            return;
        }

        $adminUser = User::where('email', 'admin@example.com')->first();
        $regularUser = User::where('email', 'user@example.com')->first();

        $todoStatus = TaskStatus::where('title', 'To Do')->first();
        $inProgressStatus = TaskStatus::where('title', 'In Progress')->first();
        $doneStatus = TaskStatus::where('title', 'Done')->first();

        $tasks = [
            [
                'title' => 'Set up project repository',
                'description' => 'Initialise the Git repository and configure CI/CD pipelines.',
                'due_date' => '2025-07-01',
                'assigned_date' => '2025-06-01',
                'assigned_to' => $adminUser?->id,
                'status_id' => $todoStatus?->id,
                'meta' => ['priority' => 'high'],
                'created_by' => $adminUser?->id,
            ],
            [
                'title' => 'Write onboarding documentation',
                'description' => 'Create documentation to help new team members get up to speed quickly.',
                'due_date' => '2025-07-15',
                'assigned_date' => '2025-06-05',
                'assigned_to' => $regularUser?->id,
                'status_id' => $inProgressStatus?->id,
                'meta' => ['priority' => 'medium'],
                'created_by' => $adminUser?->id,
            ],
            [
                'title' => 'Conduct security audit',
                'description' => 'Review all endpoints and dependencies for potential vulnerabilities.',
                'due_date' => '2025-08-01',
                'assigned_date' => '2025-06-10',
                'assigned_to' => $adminUser?->id,
                'status_id' => $todoStatus?->id,
                'meta' => ['priority' => 'high', 'tags' => ['security']],
                'created_by' => $adminUser?->id,
            ],
            [
                'title' => 'Update dependencies',
                'description' => 'Run composer and npm updates, resolve any breaking changes.',
                'due_date' => '2025-06-20',
                'assigned_date' => '2025-06-01',
                'assigned_to' => $regularUser?->id,
                'status_id' => $doneStatus?->id,
                'meta' => null,
                'created_by' => $adminUser?->id,
            ],
            [
                'title' => 'Design database schema for reporting module',
                'description' => 'Outline tables, relationships, and indexes required for the reporting module.',
                'due_date' => '2025-07-30',
                'assigned_date' => '2025-06-12',
                'assigned_to' => $adminUser?->id,
                'status_id' => $inProgressStatus?->id,
                'meta' => ['priority' => 'medium'],
                'created_by' => $adminUser?->id,
            ],
        ];

        foreach ($tasks as $task) {
            Task::create($task);
        }
    }
}
