<?php

namespace Database\Seeders;

use App\Models\InvoiceStatus;
use App\Models\User;
use Illuminate\Database\Seeder;

class InvoiceStatusSeeder extends Seeder
{
    public function run(): void
    {
        if (InvoiceStatus::exists()) {
            $this->command->info('Invoice Statuses already seeded, skipping...');

            return;
        }

        $users = User::all()->keyBy('email');

        if ($users->isEmpty()) {
            $this->command->warn('No users found, skipping address seeding...');

            return;
        }

        $statuses = [
            [
                'title' => 'Draft',
                'description' => 'Invoice created but not yet sent to the client.',
                'background_colour' => '#e5e7eb',
                'text_colour' => '#111827',
            ],
            [
                'title' => 'Pending',
                'description' => 'Invoice sent and awaiting payment.',
                'background_colour' => '#fef3c7',
                'text_colour' => '#92400e',
            ],
            [
                'title' => 'Sent',
                'description' => 'Invoice has been delivered to the client.',
                'background_colour' => '#dbeafe',
                'text_colour' => '#1e40af',
            ],
            [
                'title' => 'Paid',
                'description' => 'Invoice has been paid in full.',
                'background_colour' => '#d1fae5',
                'text_colour' => '#065f46',
            ],
            [
                'title' => 'Overdue',
                'description' => 'Invoice payment is past the due date.',
                'background_colour' => '#fee2e2',
                'text_colour' => '#991b1b',
            ],
            [
                'title' => 'Cancelled',
                'description' => 'Invoice has been cancelled and is no longer payable.',
                'background_colour' => '#f3f4f6',
                'text_colour' => '#374151',
            ],
            [
                'title' => 'Refunded',
                'description' => 'Payment has been refunded to the client.',
                'background_colour' => '#ede9fe',
                'text_colour' => '#5b21b6',
            ],
            [
                'title' => 'Void',
                'description' => 'Invoice has been voided and holds no financial value.',
                'background_colour' => '#f1f5f9',
                'text_colour' => '#475569',
            ],
        ];

        foreach ($statuses as $status) {
            InvoiceStatus::updateOrCreate(
                ['title' => $status['title']],
                $status
            );
        }
    }
}
