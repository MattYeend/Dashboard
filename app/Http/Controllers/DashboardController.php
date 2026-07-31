<?php

namespace App\Http\Controllers;

use App\Services\Dashboard\DashboardStatsService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardStatsService $dashboardStatsService
    ) {}

    /**
     * Display the dashboard with stats for the authenticated user.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('Dashboard', [
            'stats' => $this->dashboardStatsService->forUser($request->user()),
        ]);
    }
}
