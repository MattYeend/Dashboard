<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Order::exists()) {
            $this->command->info('Orders already seeded, skipping...');

            return;
        }

        $users = User::all()->keyBy('email');

        if ($users->isEmpty()) {
            $this->command->warn('No users found, skipping order seeding...');

            return;
        }

        $statuses = OrderStatus::orderBy('id')->pluck('id')->all();

        if (empty($statuses)) {
            $this->command->warn('No order statuses found, orders will be seeded without a status...');
        }

        $morphType = (new User)->getMorphClass();

        foreach ($this->getOrders($morphType, $users, $statuses) as $order) {
            Order::create($order);
        }
    }

    /**
     * Get the predefined order records to seed.
     *
     * @param  Collection<string, User>  $users
     * @param  array<int, int>  $statuses
     * @return array<int, array<string, string|int|float|null>>
     */
    private function getOrders(string $morphType, Collection $users, array $statuses): array
    {
        return [
            [
                'orderable_type' => $morphType,
                'orderable_id' => $users['superadmin@example.com']->id,
                'order_number' => 'ORD-100001',
                'title' => 'Website Redesign Project',
                'description' => 'Full redesign of the corporate website including new branding and CMS migration.',
                'notes' => 'Client requested phased delivery.',
                'subtotal' => 8500.00,
                'discount_amount' => 500.00,
                'tax_amount' => 1600.00,
                'total_amount' => 9600.00,
                'ordered_at' => now()->subMonths(2),
                'due_at' => now()->subMonth(),
                'completed_at' => now()->subMonth(),
                'status_id' => $statuses[2] ?? ($statuses[0] ?? null),
            ],
            [
                'orderable_type' => $morphType,
                'orderable_id' => $users['admin@example.com']->id,
                'order_number' => 'ORD-100002',
                'title' => 'Office Equipment Supply',
                'description' => 'Procurement of desks, chairs and monitors for the Manchester office.',
                'notes' => null,
                'subtotal' => 3200.00,
                'discount_amount' => 0.00,
                'tax_amount' => 640.00,
                'total_amount' => 3840.00,
                'ordered_at' => now()->subWeeks(3),
                'due_at' => now()->addWeek(),
                'completed_at' => null,
                'status_id' => $statuses[1] ?? ($statuses[0] ?? null),
            ],
            [
                'orderable_type' => $morphType,
                'orderable_id' => $users['john.admin@example.com']->id,
                'order_number' => 'ORD-100003',
                'title' => 'Annual Software Licence Renewal',
                'description' => 'Renewal of design and development tooling licences for the Edinburgh team.',
                'notes' => 'Auto-renew disabled, requires manual approval each year.',
                'subtotal' => 1450.00,
                'discount_amount' => 145.00,
                'tax_amount' => 261.00,
                'total_amount' => 1566.00,
                'ordered_at' => now()->subDays(10),
                'due_at' => now()->addDays(5),
                'completed_at' => null,
                'status_id' => $statuses[0] ?? null,
            ],
            [
                'orderable_type' => $morphType,
                'orderable_id' => $users['manager@example.com']->id,
                'order_number' => 'ORD-100004',
                'title' => 'Marketing Campaign Materials',
                'description' => 'Print and digital assets for the Q3 product launch campaign.',
                'notes' => null,
                'subtotal' => 2100.00,
                'discount_amount' => 0.00,
                'tax_amount' => 420.00,
                'total_amount' => 2520.00,
                'ordered_at' => now()->subDays(5),
                'due_at' => now()->addDays(20),
                'completed_at' => null,
                'status_id' => $statuses[1] ?? ($statuses[0] ?? null),
            ],
            [
                'orderable_type' => $morphType,
                'orderable_id' => $users['editor@example.com']->id,
                'order_number' => 'ORD-100005',
                'title' => 'Server Infrastructure Upgrade',
                'description' => 'Upgrade of staging and production servers to meet increased traffic demand.',
                'notes' => 'Coordinate downtime window with DevOps.',
                'subtotal' => 12000.00,
                'discount_amount' => 1000.00,
                'tax_amount' => 2200.00,
                'total_amount' => 13200.00,
                'ordered_at' => now()->subMonths(1),
                'due_at' => now()->subWeeks(1),
                'completed_at' => now()->subWeeks(1),
                'status_id' => $statuses[2] ?? ($statuses[0] ?? null),
            ],
        ];
    }
}
