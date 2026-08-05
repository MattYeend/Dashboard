<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomDashboardWidgets\StoreCustomDashboardWidgetRequest;
use App\Http\Requests\CustomDashboardWidgets\UpdateCustomDashboardWidgetRequest;
use App\Models\CustomDashboardWidget;
use App\Services\CustomDashboardWidgets\ManagementService;
use App\Support\DashboardMetricRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomDashboardWidgetController extends Controller
{
    /**
     * Inject the required services into the controller.
     */
    public function __construct(
        protected readonly ManagementService $management,
    ) {}

    /**
     * List the metrics available to build a custom widget from.
     */
    public function metrics(): JsonResponse
    {
        return response()->json([
            'metrics' => DashboardMetricRegistry::all(),
        ]);
    }

    /**
     * Store a newly created custom widget for the authenticated user.
     *
     * Validation is handled upstream by StoreCustomDashboardWidgetRequest.
     */
    public function store(StoreCustomDashboardWidgetRequest $request): JsonResponse
    {
        $widget = $this->management->store($request);

        return response()->json($widget, 201);
    }

    /**
     * Update the specified custom widget.
     *
     * Ownership and validation are both handled upstream by
     * UpdateCustomDashboardWidgetRequest.
     */
    public function update(
        UpdateCustomDashboardWidgetRequest $request,
        CustomDashboardWidget $customDashboardWidget
    ): JsonResponse {
        $widget = $this->management->update($request, $customDashboardWidget);

        return response()->json($widget);
    }

    /**
     * Permanently remove the specified custom widget.
     *
     * Ownership is checked explicitly since this route has no
     * dedicated form request to carry the check.
     */
    public function destroy(Request $request, CustomDashboardWidget $customDashboardWidget): JsonResponse
    {
        abort_unless($customDashboardWidget->user_id === $request->user()->id, 403);

        $this->management->destroy($customDashboardWidget);

        return response()->json(null, 204);
    }
}
