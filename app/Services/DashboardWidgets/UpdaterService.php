<?php

namespace App\Services\DashboardWidgets;

use App\Models\User;
use App\Support\DashboardWidgetRegistry;
use Illuminate\Support\Facades\DB;

class UpdaterService
{
    /**
     * @param  array<int, array{key: string, position: int, is_visible: bool}>  $widgets
     */
    public function updateForUser(User $user, array $widgets): void
    {
        $allowedKeys = DashboardWidgetRegistry::keys();

        DB::transaction(function () use ($user, $widgets, $allowedKeys) {
            foreach ($widgets as $widget) {
                if (! in_array($widget['key'], $allowedKeys, true)) {
                    continue;
                }

                $user->dashboardWidgetPreferences()->updateOrCreate(
                    ['widget_key' => $widget['key']],
                    [
                        'position' => $widget['position'],
                        'is_visible' => $widget['is_visible'],
                    ]
                );
            }
        });
    }
}
