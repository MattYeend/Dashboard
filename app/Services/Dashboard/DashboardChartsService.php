<?php

namespace App\Services\Dashboard;

use App\Models\User;

class DashboardChartsService
{
    public function __construct(
        protected DashboardStatsService $dashboardStatsService,
    ) {}

    /**
     * @return array<int, array{
     *     key: string,
     *     label: string,
     *     data: array<int, array{label: string, value: int}>
     * }>
     */
    public function forUser(User $user): array
    {
        $stats = $this->dashboardStatsService->forUser($user);

        $groups = [
            'tasks' => [
                'label' => 'Tasks',
                'metrics' => [
                    'completed' => 'Completed',
                    'outstanding' => 'Outstanding',
                ],
            ],
            'companies' => [
                'label' => 'Companies',
                'metrics' => [
                    'total' => 'Total',
                    'created_this_month' => 'New this month',
                ],
            ],
            'deals' => [
                'label' => 'Deals',
                'metrics' => [
                    'total' => 'Total',
                    'won' => 'Won',
                    'lost' => 'Lost',
                ],
            ],
            'pipelines' => [
                'label' => 'Pipelines',
                'metrics' => [
                    'total' => 'Total',
                    'won' => 'Won',
                    'lost' => 'Lost',
                ],
            ],
            'orders' => [
                'label' => 'Orders',
                'metrics' => [
                    'total' => 'Total',
                    'completed' => 'Completed',
                    'outstanding' => 'Outstanding',
                ],
            ],
            'invoices' => [
                'label' => 'Invoices',
                'metrics' => [
                    'total' => 'Total',
                    'paid' => 'Paid',
                    'outstanding' => 'Outstanding',
                ],
            ],
        ];

        $charts = [];

        foreach ($groups as $key => $group) {
            $values = $stats[$key] ?? [];

            $charts[] = [
                'key' => $key,
                'label' => $group['label'],
                'data' => collect($group['metrics'])
                    ->map(fn (string $label, string $metricKey) => [
                        'label' => $label,
                        'value' => $values[$metricKey] ?? 0,
                    ])
                    ->values()
                    ->all(),
            ];
        }

        return $charts;
    }
}
