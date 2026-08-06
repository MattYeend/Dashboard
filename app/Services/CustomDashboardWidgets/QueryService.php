<?php

namespace App\Services\CustomDashboardWidgets;

use App\Models\CustomDashboardWidget;
use App\Models\User;
use App\Services\DashboardWidgets\MetricCalculatorService;

class QueryService
{
    public function __construct(
        protected MetricCalculatorService $metricCalculator,
    ) {}

    /**
     * @return array<int, array{id: int, key: string, label: string, description: string|null, group: string, type: string, position: int, is_visible: bool, value: int}>
     */
    public function forUser(User $user): array
    {
        return $user->customDashboardWidgets()
            ->get()
            ->map(fn (CustomDashboardWidget $widget) => [
                'id' => $widget->id,
                'key' => 'custom_'.$widget->id,
                'label' => $widget->label,
                'description' => $widget->description,
                'group' => 'custom',
                'type' => 'custom',
                'position' => $widget->position,
                'is_visible' => $widget->is_visible,
                'value' => $this->metricCalculator->calculate($widget->metric_key, $widget->date_range),
            ])
            ->values()
            ->all();
    }
}
