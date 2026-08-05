<?php

namespace App\Http\Controllers;

use App\Http\Requests\Dashboard\UpdateDashboardWidgetPreferencesRequest;
use App\Services\DashboardWidgets\QueryService;
use App\Services\DashboardWidgets\UpdaterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardWidgetPreferenceController extends Controller
{
    /**
     * Inject the required services into the controller.
     */
    public function __construct(
        protected readonly QueryService $queryService,
        protected readonly UpdaterService $updaterService,
    ) {}

    /**
     * Display the authenticated user's dashboard widget layout.
     *
     * Merges the widget registry with the user's stored preferences,
     * defaulting any widget the user hasn't touched yet to visible.
     */
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'widgets' => $this->queryService->forUser($request->user()),
        ]);
    }

    /**
     * Update the authenticated user's dashboard widget layout.
     *
     * Validation is handled upstream by UpdateDashboardWidgetPreferencesRequest.
     *
     * Only the authenticated user's own preference rows are ever written to,
     * regardless of what is submitted in the request body.
     */
    public function update(UpdateDashboardWidgetPreferencesRequest $request): JsonResponse
    {
        $this->updaterService->updateForUser(
            $request->user(),
            $request->validated('widgets')
        );

        return response()->json([
            'widgets' => $this->queryService->forUser($request->user()),
        ]);
    }
}
