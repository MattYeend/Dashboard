<?php

namespace App\Services\CustomDashboardWidgets;

use App\Http\Requests\CustomDashboardWidgets\StoreCustomDashboardWidgetRequest;
use App\Http\Requests\CustomDashboardWidgets\UpdateCustomDashboardWidgetRequest;
use App\Models\CustomDashboardWidget;

class ManagementService
{
    /**
     * Inject the required services into the management service.
     */
    public function __construct(
        protected CreatorService $creator,
        protected UpdaterService $updater,
        protected DeleterService $deleter,
    ) {}

    /**
     * Create a new custom dashboard widget for the authenticated user.
     *
     * Validation is handled upstream by StoreCustomDashboardWidgetRequest.
     */
    public function store(StoreCustomDashboardWidgetRequest $request): CustomDashboardWidget
    {
        return $this->creator->create($request->user(), $request->validated());
    }

    /**
     * Update an existing custom dashboard widget.
     *
     * Ownership and validation are both handled upstream by
     * UpdateCustomDashboardWidgetRequest.
     */
    public function update(UpdateCustomDashboardWidgetRequest $request, CustomDashboardWidget $widget): CustomDashboardWidget
    {
        return $this->updater->update($widget, $request->validated());
    }

    /**
     * Permanently remove a custom dashboard widget.
     */
    public function destroy(CustomDashboardWidget $widget): void
    {
        $this->deleter->delete($widget);
    }
}
