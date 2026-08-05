<?php

namespace App\Support;

use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Pipeline;
use App\Models\Post;
use App\Models\Task;

class DashboardMetricRegistry
{
    /**
     * @return array<int, array{key: string, label: string, model: class-string}>
     */
    public static function all(): array
    {
        return [
            ['key' => 'companies_count', 'label' => 'Companies', 'model' => Company::class],
            ['key' => 'contacts_count', 'label' => 'Contacts', 'model' => Contact::class],
            ['key' => 'orders_count', 'label' => 'Orders', 'model' => Order::class],
            ['key' => 'invoices_count', 'label' => 'Invoices', 'model' => Invoice::class],
            ['key' => 'posts_count', 'label' => 'Posts', 'model' => Post::class],
            ['key' => 'deals_count', 'label' => 'Deals', 'model' => Deal::class],
            ['key' => 'pipelines_count', 'label' => 'Pipelines', 'model' => Pipeline::class],
            ['key' => 'tasks_count', 'label' => 'Tasks', 'model' => Task::class],
        ];
    }

    /**
     * @return string[]
     */
    public static function keys(): array
    {
        return array_column(self::all(), 'key');
    }

    public static function modelFor(string $key): ?string
    {
        return collect(self::all())->firstWhere('key', $key)['model'] ?? null;
    }
}
