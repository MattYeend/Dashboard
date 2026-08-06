<?php

namespace App\Services\CustomDashboardWidgets;

use App\Models\CustomDashboardWidget;
use App\Models\User;

class CreatorService
{
    /**
     * @param  array{label: string, description?: string|null, metric_key: string, date_range: string}  $data
     */
    public function create(User $user, array $data): CustomDashboardWidget
    {
        $nextPosition = (int) $user->customDashboardWidgets()->max('position') + 1;

        return $user->customDashboardWidgets()->create([
            ...$data,
            'position' => $nextPosition,
            'is_visible' => true,
        ]);
    }
}
