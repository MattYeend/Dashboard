<?php

namespace App\Http\Controllers;

use App\Services\Dashboard\DashboardStatsService;
use App\Services\DashboardWidgets\QueryService as DashboardWidgetQueryService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

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
        return Inertia::render('Dashboard', [
            'stats' => $this->dashboardStatsService->forUser($request->user()),
            'widgets' => $this->dashboardWidgetQueryService->forUser($request->user()),
        ]);
    }
}
