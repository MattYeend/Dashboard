<?php

namespace App\Services\CustomDashboardWidgets;

use App\Models\CustomDashboardWidget;

class DeleterService
{
    public function delete(CustomDashboardWidget $widget): void
    {
        $widget->delete();
    }
}
