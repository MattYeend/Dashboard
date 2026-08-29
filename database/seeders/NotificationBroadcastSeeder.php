<?php

namespace Database\Seeders;

use App\Models\NotificationBroadcast;
use App\Models\User;
use Illuminate\Database\Seeder;

class NotificationBroadcastSeeder extends Seeder
{
    public function run(): void
    {
        if (NotificationBroadcast::exists()) {
            $this->command->info('Notification broadcasts already seeded, skipping...');

            return;
        }

        $admin = User::where('email', 'admin@example.com')->first() ?? User::first();

        if ($admin === null) {
            $this->command->warn('No users found, skipping notification broadcast seeding...');

            return;
        }

        $broadcasts = [
            [
                'title' => 'Scheduled maintenance this weekend',
                'body' => 'The dashboard will be unavailable on Saturday between 01:00 and 03:00 for scheduled database maintenance.',
                'audience_type' => 'all',
                'audience_ids' => null,
                'sent_at' => null,
                'sent_by' => null,
            ],
            [
                'title' => 'New Pipelines module released',
                'body' => 'You can now track deals through custom pipeline stages from the Pipelines section in the sidebar.',
                'audience_type' => 'role',
                'audience_ids' => ['admin', 'super_admin'],
                'sent_at' => now()->subDays(2),
                'sent_by' => $admin->id,
            ],
        ];

        foreach ($broadcasts as $data) {
            NotificationBroadcast::updateOrCreate(
                ['title' => $data['title']],
                [
                    'body' => $data['body'],
                    'audience_type' => $data['audience_type'],
                    'audience_ids' => $data['audience_ids'],
                    'sent_at' => $data['sent_at'],
                    'sent_by' => $data['sent_by'],
                    'created_by' => $admin->id,
                    'updated_by' => $admin->id,
                ]
            );
        }
    }
}
