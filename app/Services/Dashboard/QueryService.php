<?php

namespace App\Services\Dashboard;

use App\Models\Company;
use App\Models\Contact;
use App\Models\Log;
use App\Models\Order;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Raw data retrieval for the dashboard. Deliberately dumb - no formatting or
 * shaping for the frontend happens here, that's FormatterService's job.
 */
class QueryService
{
    /**
     * Headline counts shown on the summary cards.
     *
     * Only counts live (non-trashed) rows across the established modules -
     * Deals/Invoices aren't included yet as those modules are still being
     * built out.
     */
    public function getSummaryCounts(): array
    {
        return [
            'users' => User::query()->count(),
            'companies' => Company::query()->count(),
            'contacts' => Contact::query()->count(),
            'orders' => Order::query()->count(),
            'tasks' => Task::query()->count(),
        ];
    }

    /**
     * Task counts grouped by status, including each status's display colours
     * so the frontend doesn't need a second lookup for the chart legend.
     */
    public function getTaskStatusBreakdown(): Collection
    {
        return TaskStatus::query()
            ->withCount('tasks')
            ->orderBy('title')
            ->get(['id', 'title', 'background_colour', 'text_colour']);
    }

    /**
     * Revenue totals grouped by calendar month, most recent $months back.
     *
     * Uses orders.total_amount (the post-discount, post-tax figure) rather
     * than subtotal, and groups by ordered_at rather than created_at since
     * an order can be entered after the date it was actually placed.
     */
    public function getOrdersByMonth(int $months = 12): Collection
    {
        $from = Carbon::now()->subMonths($months - 1)->startOfMonth();

        return Order::query()
            ->selectRaw('DATE_FORMAT(ordered_at, "%Y-%m") as period, SUM(total_amount) as total')
            ->whereNotNull('ordered_at')
            ->where('ordered_at', '>=', $from)
            ->groupBy('period')
            ->orderBy('period')
            ->get();
    }

    /**
     * Most recent audit log entries, for a "recent activity" feed if the
     * frontend wants one later.
     */
    public function getRecentAuditLogs(int $limit = 10): Collection
    {
        return Log::query()
            ->with('user:id,name')
            ->latest()
            ->limit($limit)
            ->get();
    }
}
