<?php

namespace App\Http\Controllers\Logs;

use App\Http\Controllers\Controller;
use App\Http\Requests\Logs\IndexLogRequest;
use App\Models\Log;
use App\Services\Logs\QueryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;
use Inertia\Response;

class LogController extends Controller
{
    use AuthorizesRequests;

    /**
     * Create a new controller instance.
     */
    public function __construct(
        private readonly QueryService $queryService
    ) {}

    /**
     * Display the audit trail.
     */
    public function index(IndexLogRequest $request): Response
    {
        $this->authorize('viewAny', Log::class);

        return Inertia::render('AuditTrail/Index', $this->queryService->getPaginated($request->user(), $request->validated()));
    }

    /**
     * Display one audit entry.
     */
    public function show(Log $log): Response
    {
        $this->authorize('view', $log);

        $log->loadMissing(['loggedInUser', 'relatedToUser']);

        return Inertia::render('AuditTrail/Show', ['log' => $log]);
    }
}
