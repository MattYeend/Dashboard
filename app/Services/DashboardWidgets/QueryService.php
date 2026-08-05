<?php

namespace App\Services\DashboardWidgets;

use App\Models\User;
use App\Services\CustomDashboardWidgets\QueryService as CustomDashboardWidgetQueryService;
use App\Support\DashboardWidgetRegistry;

class QueryService
{
    public function __construct(
        protected CustomDashboardWidgetQueryService $customWidgetQuery,
    ) {}

    /**
     * Merge the built-in registry, the user's preferences, and the
     * user's custom widgets into a single ordered layout.
     *
     * @return array<int, array<string, mixed>>
     */
    public function forUser(User $user): array
    {
        $preferences = $user->dashboardWidgetPreferences()
            ->get()
            ->keyBy('widget_key');

        $builtIn = collect(DashboardWidgetRegistry::all())
            ->map(function (array $widget, int $index) use ($preferences) {
                $preference = $preferences->get($widget['key']);

                return [
                    ...$widget,
                    'type' => 'builtin',
                    'position' => $preference?->position ?? $index,
                    'is_visible' => $preference?->is_visible ?? true,
                ];
            });

        $custom = collect($this->customWidgetQuery->forUser($user));

        return $builtIn->concat($custom)
            ->sortBy('position')
            ->values()
            ->all();
    }
}
