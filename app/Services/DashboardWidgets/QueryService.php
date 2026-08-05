<?php

namespace App\Services\DashboardWidgets;

use App\Models\User;
use App\Support\DashboardWidgetRegistry;

class QueryService
{
    /**
     * Merge the registry with the user's stored preferences.
     * Widgets the user hasn't touched yet default to visible, in registry order.
     *
     * @return array<int, array{key: string, label: string, group: string, position: int, is_visible: bool}>
     */
    public function forUser(User $user): array
    {
        $preferences = $user->dashboardWidgetPreferences()
            ->get()
            ->keyBy('widget_key');

        return collect(DashboardWidgetRegistry::all())
            ->map(function (array $widget, int $index) use ($preferences) {
                $preference = $preferences->get($widget['key']);

                return [
                    ...$widget,
                    'position' => $preference?->position ?? $index,
                    'is_visible' => $preference?->is_visible ?? true,
                ];
            })
            ->sortBy('position')
            ->values()
            ->all();
    }
}
