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
            ['key' => 'tasks_completed', 'label' => 'Tasks completed', 'group' => 'Productivity'],
            ['key' => 'tasks_outstanding', 'label' => 'Tasks outstanding', 'group' => 'Productivity'],
            ['key' => 'companies', 'label' => 'Companies', 'group' => 'CRM'],
            ['key' => 'deals_created', 'label' => 'Deals created', 'group' => 'CRM'],
            ['key' => 'deals_won', 'label' => 'Deals won', 'group' => 'CRM'],
            ['key' => 'pipelines_total', 'label' => 'Pipelines', 'group' => 'CRM'],
            ['key' => 'pipelines_won', 'label' => 'Pipelines won', 'group' => 'CRM'],
            ['key' => 'orders', 'label' => 'Orders', 'group' => 'Sales'],
            ['key' => 'invoices', 'label' => 'Invoices', 'group' => 'Sales'],
            ['key' => 'latest_posts', 'label' => 'Latest posts', 'group' => 'Content'],
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
