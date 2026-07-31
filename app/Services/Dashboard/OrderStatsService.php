<?php

namespace App\Services\Dashboard;

use App\Models\Order;

class OrderStatsService
{
    /**
     * Get total, completed and outstanding order counts.
     *
     * Completion is determined by OrderStatus title: "Delivered" counts
     * as completed, everything except "Delivered", "Cancelled",
     * "Refunded" and "Failed" counts as outstanding.
     *
     * @return array{total: int, completed: int, outstanding: int}
     */
    public function summary(): array
    {
        $total = Order::query()->count();

        $completed = Order::query()
            ->whereHas('status', fn ($query) => $query->where('title', 'Delivered'))
            ->count();

        $outstanding = Order::query()
            ->whereHas('status', fn ($query) => $query->whereNotIn('title', [
                'Delivered', 'Cancelled', 'Refunded', 'Failed',
            ]))
            ->count();

        return [
            'total' => $total,
            'completed' => $completed,
            'outstanding' => $outstanding,
        ];
    }
}
