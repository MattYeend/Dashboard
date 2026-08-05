<?php

namespace App\Services\DashboardWidgets;

use App\Enums\DashboardDateRange;
use App\Support\DashboardMetricRegistry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class MetricCalculatorService
{
    /**
     * Calculate the current value for a given metric and date range.
     * Only models registered in DashboardMetricRegistry can be queried,
     * so an arbitrary or tampered metric_key simply resolves to zero.
     */
    public function calculate(string $metricKey, string $dateRange): int
    {
        $modelClass = DashboardMetricRegistry::modelFor($metricKey);

        if ($modelClass === null) {
            return 0;
        }

        $query = $modelClass::query();

        $this->applyDateRange($query, DashboardDateRange::from($dateRange));

        return $query->count();
    }

    /**
     * Constrain the query to the given date range.
     *
     * All time is a no-op — the query is returned unfiltered.
     */
    protected function applyDateRange(Builder $query, DashboardDateRange $dateRange): void
    {
        match ($dateRange) {
            DashboardDateRange::Today => $query->whereDate('created_at', Carbon::today()),
            DashboardDateRange::ThisWeek => $query->whereBetween('created_at', [
                Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek(),
            ]),
            DashboardDateRange::ThisMonth => $query->whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year),
            DashboardDateRange::ThisYear => $query->whereYear('created_at', Carbon::now()->year),
            DashboardDateRange::AllTime => null,
        };
    }
}
