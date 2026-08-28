<?php

namespace App\Http\Controllers;

use App\Actions\ExportDashboardSummary;
use App\Services\Dashboard\DashboardChartsService;
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
        protected DashboardChartsService $dashboardChartsService,
    ) {}

    /**
     * Display the dashboard with stats, charts and widgets for the
     * authenticated user. Gated by 'view dashboard' at the route level.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('Dashboard', [
            'stats' => $this->dashboardStatsService->forUser($user),
            'widgets' => $this->dashboardWidgetQueryService->forUser($user),
            'metrics' => DashboardMetricRegistry::all(),
            'charts' => $this->dashboardChartsService->forUser($user),
            'permissions' => [
                'can_view_statistics' => $user->can('view statistics'),
                'can_view_charts' => $user->can('view charts'),
                'can_export' => $user->can('export dashboard data'),
            ],
        ]);
    }

    /**
     * JSON endpoint for refreshing just the stats. Gated by 'view statistics'
     * at the route level, independent of the main 'view dashboard' gate.
     */
    public function statistics(Request $request): JsonResponse
    {
        return response()->json(
            $this->dashboardStatsService->forUser($request->user())
        );
    }

    /**
     * JSON endpoint for refreshing just the chart data. Gated by
     * 'view charts' at the route level.
     */
    public function charts(Request $request): JsonResponse
    {
        return response()->json(
            $this->dashboardChartsService->forUser($request->user())
        );
    }

    /**
     * Streams the dashboard stats as a CSV download. Gated by
     * 'export dashboard data' at the route level.
     */
    public function export(
        Request $request,
        ExportDashboardSummary $action,
    ): StreamedResponse {
        $rows = $action->handle(
            $this->dashboardStatsService->forUser($request->user())
        );

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, 'dashboard-summary-'.now()->format('Y-m-d').'.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }
}
