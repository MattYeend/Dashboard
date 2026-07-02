<?php

namespace Database\Seeders;

use App\Models\OrderStatus;
use Illuminate\Database\Seeder;

class OrderStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (OrderStatus::exists()) {
            $this->command->info('Order statuses already seeded, skipping...');

            return;
        }

        $statuses = [
            [
                'title' => 'Pending',
                'description' => 'Order has been placed but payment has not yet been confirmed.',
                'background_colour' => '#e2e8f0',
                'text_colour' => '#1a202c',
                'meta' => null,
            ],
            [
                'title' => 'Processing',
                'description' => 'Payment has been confirmed and the order is being prepared.',
                'background_colour' => '#bee3f8',
                'text_colour' => '#2b6cb0',
                'meta' => null,
            ],
            [
                'title' => 'Shipped',
                'description' => 'Order has left the warehouse and is on its way to the customer.',
                'background_colour' => '#d6bcfa',
                'text_colour' => '#553c9a',
                'meta' => null,
            ],
            [
                'title' => 'Delivered',
                'description' => 'Order has been delivered to the customer.',
                'background_colour' => '#c6f6d5',
                'text_colour' => '#22543d',
                'meta' => null,
            ],
            [
                'title' => 'On Hold',
                'description' => 'Order has been paused due to a stock, payment, or fulfilment issue.',
                'background_colour' => '#fefcbf',
                'text_colour' => '#744210',
                'meta' => null,
            ],
            [
                'title' => 'Cancelled',
                'description' => 'Order has been cancelled and will not be fulfilled.',
                'background_colour' => '#e2e8f0',
                'text_colour' => '#718096',
                'meta' => null,
            ],
            [
                'title' => 'Refunded',
                'description' => 'Order has been returned and the customer has been refunded.',
                'background_colour' => '#fed7d7',
                'text_colour' => '#742a2a',
                'meta' => null,
            ],
            [
                'title' => 'Failed',
                'description' => 'Order could not be completed due to a payment or processing failure.',
                'background_colour' => '#feb2b2',
                'text_colour' => '#822727',
                'meta' => null,
            ],
        ];

        foreach ($statuses as $status) {
            OrderStatus::firstOrCreate(
                ['title' => $status['title']],
                $status
            );
        }
    }
}