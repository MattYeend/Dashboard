<?php

namespace App\Services\Dashboard;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Shapes QueryService's raw output into the flat arrays the Vue components
 * expect - no Eloquent objects or API Resource classes cross this boundary.
 */
class FormatterService
{
    /**
     * Summary counts are already a flat array, passed through unchanged.
     * Kept as its own method so index()/statistics() share one code path.
     */
    public function formatSummaryCounts(array $counts): array
    {
        return $counts;
    }

    /**
     * Converts the TaskStatus collection into {label, value, colours} shape
     * for a doughnut chart.
     */
    public function formatTaskStatusChart(Collection $breakdown): array
    {
        return $breakdown->map(fn ($status) => [
            'label' => $status->title,
            'value' => $status->tasks_count,
            'background_colour' => $status->background_colour,
            'text_colour' => $status->text_colour,
        ])->values()->all();
    }

    /**
     * Fills in any months with no orders as zero, so the bar chart always
     * shows a full 12-month (or however many requested) run rather than
     * skipping gaps.
     */
    public function formatRevenueChart(Collection $ordersByMonth, int $months = 12): array
    {
        $totals = $ordersByMonth->pluck('total', 'period');
        $points = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $period = Carbon::now()->subMonths($i)->format('Y-m');

            $points[] = [
                'label' => Carbon::createFromFormat('Y-m', $period)->format('M Y'),
                'value' => (float) ($totals[$period] ?? 0),
            ];
        }

        return $points;
    }

    /**
     * Flattens audit log rows for a recent-activity feed.
     */
    public function formatAuditFeed(Collection $logs): array
    {
        return $logs->map(fn ($log) => [
            'id' => $log->id,
            'action' => $log->action,
            'user' => $log->user?->name ?? 'System',
            'created_at' => $log->created_at->toIso8601String(),
        ])->all();
    }
}
