<?php

namespace App\Support;

class DashboardWidgetRegistry
{
    /**
     * @return array<int, array{key: string, label: string, group: string}>
     */
    public static function all(): array
    {
        return [
            ['key' => 'tasks_completed', 'label' => 'Tasks completed', 'group' => 'productivity'],
            ['key' => 'tasks_outstanding', 'label' => 'Tasks outstanding', 'group' => 'productivity'],
            ['key' => 'companies', 'label' => 'Companies', 'group' => 'crm'],
            ['key' => 'deals_created', 'label' => 'Deals created', 'group' => 'crm'],
            ['key' => 'deals_won', 'label' => 'Deals won', 'group' => 'crm'],
            ['key' => 'pipelines_total', 'label' => 'Pipelines', 'group' => 'crm'],
            ['key' => 'pipelines_won', 'label' => 'Pipelines won', 'group' => 'crm'],
            ['key' => 'orders', 'label' => 'Orders', 'group' => 'sales'],
            ['key' => 'invoices', 'label' => 'Invoices', 'group' => 'sales'],
            ['key' => 'latest_posts', 'label' => 'Latest posts', 'group' => 'content'],
        ];
    }

    /**
     * @return string[]
     */
    public static function keys(): array
    {
        return array_column(self::all(), 'key');
    }
}
