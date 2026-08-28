<?php

namespace App\Http\Controllers;

use App\Actions\ExportDashboardSummary;
use App\Services\Dashboard\DashboardStatsService;
use App\Services\DashboardWidgets\QueryService as DashboardWidgetQueryService;
use App\Support\DashboardMetricRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardStatsService $dashboardStatsService,
        protected DashboardWidgetQueryService $dashboardWidgetQueryService,
    ) {}

    /**
     * Display the dashboard with stats for the authenticated user.
     */
    public function index(Request $request): Response
    {
        abort_unless($request->user()->can('view dashboard'), 403);

        return Inertia::render('Dashboard', [
            'stats' => $this->dashboardStatsService->forUser($request->user()),
            'widgets' => $this->dashboardWidgetQueryService->forUser($request->user()),
            'metrics' => DashboardMetricRegistry::all(),
        ]);
    }

    /**
     * JSON endpoint for refreshing just the stats, gated separately from
     * the main dashboard so a user could have stats access without full
     * dashboard access.
     *
     * NOTE: reuses DashboardStatsService::forUser() as-is - if there's a
     * lighter/different payload intended for standalone stats refreshes,
     * swap this for the appropriate method.
     */
    public function statistics(Request $request): JsonResponse
    {
        abort_unless($request->user()->can('view statistics'), 403);

        return response()->json(
            $this->dashboardStatsService->forUser($request->user())
        );
    }

    /**
     * JSON endpoint for refreshing just the chart/widget data.
     *
     * NOTE: reuses DashboardWidgetQueryService::forUser() as-is - same
     * caveat as statistics() above if a narrower "charts only" method
     * exists or should be added.
     */
    public function charts(Request $request): JsonResponse
    {
        abort_unless($request->user()->can('view charts'), 403);

        return response()->json(
            $this->dashboardWidgetQueryService->forUser($request->user())
        );
    }

    /**
     * Streams the dashboard stats as a CSV download. Streamed rather than
     * built in memory first, so this scales if more metrics are added.
     */
    public function export(Request $request, ExportDashboardSummary $action): StreamedResponse
    {
        abort_unless($request->user()->can('export dashboard data'), 403);

        $rows = $action->handle(
            $this->dashboardStatsService->forUser($request->user())
        );

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, 'dashboard-summary-' . now()->format('Y-m-d') . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }
}
