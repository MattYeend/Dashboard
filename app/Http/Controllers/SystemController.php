<?php

namespace App\Http\Controllers;

use App\Http\Requests\Systems\EnableMaintenanceModeRequest;
use App\Services\System\SystemManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class SystemController extends Controller
{
    public function __construct(protected SystemManagementService $systemManagementService) {}

    /**
     * Show the system maintenance page.
     */
    public function index(): Response
    {
        return Inertia::render('System/Index', $this->pageProps());
    }

    /**
     * Clear the application cache.
     */
    public function clearCache(): RedirectResponse
    {
        $this->systemManagementService->clearCache(Auth::user());

        return back()->with('success', 'Application cache cleared.');
    }

    /**
     * Enable maintenance mode.
     *
     * Returns the page directly rather than redirecting - the very next
     * request from this browser would otherwise hit the 503 page before
     * the bypass link had a chance to render.
     */
    public function enableMaintenance(EnableMaintenanceModeRequest $request): Response
    {
        $secret = $this->systemManagementService->enableMaintenanceMode(
            Auth::user(),
            $request->validated(),
        );

        return Inertia::render('System/Index', [
            ...$this->pageProps(),
            'maintenanceBypassUrl' => url("/{$secret}"),
        ]);
    }

    /**
     * Disable maintenance mode.
     */
    public function disableMaintenance(): RedirectResponse
    {
        $this->systemManagementService->disableMaintenanceMode(Auth::user());

        return back()->with('success', 'Maintenance mode disabled.');
    }

    /**
     * Shared props for the system page.
     *
     * @return array<string, mixed>
     */
    protected function pageProps(): array
    {
        return [
            'systemInfo' => $this->systemManagementService->getSystemInfo(),
            'permissions' => [
                'can_clear_cache' => Gate::allows('clear cache'),
                'can_run_maintenance' => Gate::allows('run maintenance'),
                'can_view_logs' => Gate::allows('view logs'),
            ],
        ];
    }
}
