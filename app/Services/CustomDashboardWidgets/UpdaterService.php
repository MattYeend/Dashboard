<?php

namespace App\Services\CustomDashboardWidgets;

use App\Models\CustomDashboardWidget;

class UpdaterService
{
    /**
     * Update the given widget's attributes and return the fresh instance.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(CustomDashboardWidget $widget, array $data): CustomDashboardWidget
    {
        $widget->update($data);

        return $widget->fresh();
    }
}
