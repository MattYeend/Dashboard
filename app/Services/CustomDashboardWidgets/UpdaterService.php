<?php

namespace App\Services\CustomDashboardWidgets;

use App\Models\CustomDashboardWidget;

class UpdaterService
{
    public function update(CustomDashboardWidget $widget, array $data): CustomDashboardWidget
    {
        $widget->update($data);

        return $widget->fresh();
    }
}
