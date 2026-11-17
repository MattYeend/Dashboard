<?php

declare(strict_types=1);

namespace App\Services\DashboardWidgets;

use App\Models\DashboardWidgetPreference;
use App\Models\User;
use App\Services\CustomDashboardWidgets\QueryService as CustomDashboardWidgetQueryService;
use App\Support\DashboardWidgetRegistry;

class QueryService
{
    public function __construct(
        protected readonly CustomDashboardWidgetQueryService $customWidgetQuery,
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
                $hasPreference = $preference instanceof DashboardWidgetPreference;

                return [
                    ...$widget,
                    'type' => 'builtin',
                    'position' => $hasPreference ? $preference->position : $index,
                    'is_visible' => $hasPreference ? $preference->is_visible : true,
                ];
            });

        $custom = collect($this->customWidgetQuery->forUser($user));

        return $builtIn->concat($custom)
            ->sortBy('position')
            ->values()
            ->all();
    }
}
