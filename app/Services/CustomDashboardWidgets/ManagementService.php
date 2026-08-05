<?php

namespace App\Services\CustomDashboardWidgets;

use App\Http\Requests\CustomDashboardWidgets\StoreCustomDashboardWidgetRequest;
use App\Http\Requests\CustomDashboardWidgets\UpdateCustomDashboardWidgetRequest;
use App\Models\CustomDashboardWidget;

class ManagementService
{
    public function __construct(
        protected CreatorService $creator,
        protected UpdaterService $updater,
        protected DeleterService $deleter,
    ) {}

    public function store(StoreCustomDashboardWidgetRequest $request): CustomDashboardWidget
    {
        return $this->creator->create($request->user(), $request->validated());
    }

    public function update(UpdateCustomDashboardWidgetRequest $request, CustomDashboardWidget $widget): CustomDashboardWidget
    {
        return $this->updater->update($widget, $request->validated());
    }

    public function destroy(CustomDashboardWidget $widget): void
    {
        $this->deleter->delete($widget);
    }
}
