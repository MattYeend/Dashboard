<?php

namespace App\Services\Dashboard;

use App\Models\User;

class DashboardChartsService
{
    public function __construct(
        protected DealStatsService $dealStatsService,
    ) {}

    /**
     * Build the dashboard chart data for the given user.
     *
     * @return array<int, array{
     *     key: string,
     *     label: string,
     *     data: array<int, array{label: string, value: int}>
     * }>
     */
    public function forUser(User $user): array
    {
        $deals = $this->dealStatsService->summary();

        return [
            [
                'key' => 'deals',
                'label' => 'Deals',
                'data' => [
                    [
                        'label' => 'Total',
                        'value' => $deals['total'],
                    ],
                    [
                        'label' => 'Won',
                        'value' => $deals['won'],
                    ],
                    [
                        'label' => 'Lost',
                        'value' => $deals['lost'],
                    ],
                ],
            ],
        ];
    }
}
