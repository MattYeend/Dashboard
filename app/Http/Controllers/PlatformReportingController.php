<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Services\AuditLogService;
use App\Services\Platform\ReportingService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PlatformReportingController extends Controller
{
    /**
     * Inject the required services into the platform reporting controller.
     */
    public function __construct(
        protected ReportingService $reportingService,
        protected AuditLogService $auditLogService,
    ) {}

    /**
     * Show the platform-level aggregated reporting view.
     */
    public function index(Request $request): Response
    {
        $metrics = $this->reportingService->aggregate();

        $this->auditLogService->record(
            Log::ACTION_VIEW_PLATFORM_REPORTING,
            $request->user(),
            null,
            ['before' => null, 'after' => null],
        );

        return Inertia::render('Platform/Reporting', [
            'metrics' => $metrics,
        ]);
    }
}
