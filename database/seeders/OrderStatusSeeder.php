<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\OrderStatus;
use Database\Seeders\Concerns\ResolvesDefaultOrganisation;
use Database\Seeders\Concerns\SeedsSystemStatuses;
use Illuminate\Database\Seeder;

class OrderStatusSeeder extends Seeder
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
                'system_key' => OrderStatus::KEY_PENDING,
                'title' => 'Pending',
                'description' => 'Order has been placed but payment has not yet been confirmed.',
                'background_colour' => '#e2e8f0',
                'text_colour' => '#1a202c',
                'meta' => null,
            ],
            [
                'system_key' => OrderStatus::KEY_PROCESSING,
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
                'system_key' => OrderStatus::KEY_DELIVERED,
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
                'system_key' => OrderStatus::KEY_CANCELLED,
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

        $this->seedSystemStatuses(OrderStatus::class, (int) $organisation->id, $statuses);
    }
}
