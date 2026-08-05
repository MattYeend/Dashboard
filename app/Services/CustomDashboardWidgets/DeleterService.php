<?php

namespace App\Services\CustomDashboardWidgets;

use App\Models\CustomDashboardWidget;

class DeleterService
{
    /**
     * Permanently remove a custom dashboard widget.
     */
    public function delete(CustomDashboardWidget $widget): void
    {
        $widget->delete();
    }
}
