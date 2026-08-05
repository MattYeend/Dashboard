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
            ['key' => 'tasks', 'label' => 'Tasks', 'group' => 'productivity'],
            ['key' => 'companies', 'label' => 'Companies', 'group' => 'crm'],
            ['key' => 'deals', 'label' => 'Deals', 'group' => 'crm'],
            ['key' => 'pipelines', 'label' => 'Pipelines', 'group' => 'crm'],
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
