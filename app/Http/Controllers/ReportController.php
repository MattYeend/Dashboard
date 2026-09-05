<?php

namespace App\Http\Controllers;

use App\Http\Requests\Reports\ImportReportRequest;
use App\Http\Requests\Reports\StoreReportRequest;
use App\Http\Requests\Reports\UpdateReportRequest;
use App\Models\Report;
use App\Services\Reports\ManagementService;
use App\Services\Reports\QueryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    use AuthorizesRequests;

    /**
     * Inject the required services into the controller.
     */
    public function __construct(
        protected readonly ManagementService $management,
        protected readonly QueryService $query,
    ) {}

    /**
     * Display a listing of the resource.
     *
     * Passes paginated reports to the Reports/Index Inertia page.
     *
     * Authorises via the 'viewAny' policy before returning data.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Report::class);

        $data = $this->query->getPaginated(
            $request->user(),
            $request->only([
                'search', 
                'type', 
                'format', 
                'is_scheduled', 
                'sort_by', 
                'sort_direction', 
                'trashed', 
                'per_page'
                ])
        );

        return Inertia::render('Reports/Index', $data);
    }

    /**
     * Show the form for creating a new report.
     *
     * Authorises via the 'create' policy before rendering.
     */
    public function create(): Response
    {
        $this->authorize('create', Report::class);

        return Inertia::render('Reports/Create', array_merge(
            $this->query->getFormData(),
            ['canSchedule' => request()->user()->can('schedule', Report::class)],
        ));
    }

    /**
     * Store a newly created resource in storage.
     *
     * Validation is handled upstream by StoreReportRequest.
     */
    public function store(StoreReportRequest $request): JsonResponse|RedirectResponse
    {
        $report = $this->management->store($request);

        if ($request->wantsJson()) {
            return response()->json($report, 201);
        }

        return redirect()->route('reports.show', $report->id);
    }

    /**
     * Display the specified resource.
     *
     * Passes a single report to the Reports/Show Inertia page.
     *
     * Authorises via the 'view' policy before rendering.
     */
    public function show(Report $report, Request $request): Response
    {
        $this->authorize('view', $report);

        $data = $this->query->getById($request->user(), $report->id);

        return Inertia::render('Reports/Show', $data);
    }

    /**
     * Show the form for editing an existing report.
     *
     * Authorises via the 'update' policy before rendering.
     */
    public function edit(Report $report, Request $request): Response
    {
        $this->authorize('update', $report);

        $data = array_merge(
            $this->query->getById($request->user(), $report->id),
            $this->query->getFormData(),
            ['canSchedule' => $request->user()->can('schedule', Report::class)],
        );

        return Inertia::render('Reports/Edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * Validation is handled upstream by UpdateReportRequest, which also
     * implicitly authorises the operation via its authorize() method.
     */
    public function update(
        UpdateReportRequest $request, 
        Report $report
        ): JsonResponse|RedirectResponse {
        $report = $this->management->update($request, $report);

        if ($request->wantsJson()) {
            return response()->json($report);
        }

        return redirect()->route('reports.show', $report->id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * Authorises via the 'delete' policy before proceeding.
     */
    public function destroy(
        Request $request, 
        Report $report
        ): JsonResponse|RedirectResponse {
        $this->authorize('delete', $report);

        $this->management->destroy($report, $request->user());

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('reports.index');
    }

    /**
     * Restore a soft-deleted report.
     *
     * Resolves the trashed model manually since route model binding
     * excludes soft-deleted records by default.
     *
     * Authorises via the 'restore' policy before proceeding.
     */
    public function restore(
        int $id, 
        Request $request
        ): JsonResponse|RedirectResponse {
        $report = Report::onlyTrashed()->findOrFail($id);

        $this->authorize('restore', $report);

        $this->management->restore($id, $request->user());

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('reports.index');
    }

    /**
     * Permanently delete a soft-deleted report.
     *
     * Resolves the trashed model manually since route model binding
     * excludes soft-deleted records by default.
     *
     * Authorises via the 'forceDelete' policy before proceeding.
     */
    public function forceDelete(
        int $id, 
        Request $request
        ): JsonResponse|RedirectResponse {
        $report = Report::onlyTrashed()->findOrFail($id);

        $this->authorize('forceDelete', $report);

        $this->management->forceDelete($id, $request->user());

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('reports.index');
    }

    /**
     * Bulk soft-delete multiple reports.
     *
     * Authorises each report individually via the 'delete' policy.
     */
    public function bulkDelete(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['required', 'integer'],
        ]);

        $result = $this->management->bulkDelete(
            $validated['ids'],
            $request->user(),
            fn (Report $report) => $this->authorize('delete', $report)
        );

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()->route('reports.index');
    }

    /**
     * Bulk restore multiple soft-deleted reports.
     *
     * Authorises each report individually via the 'restore' policy.
     */
    public function bulkRestore(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['required', 'integer'],
        ]);

        $result = $this->management->bulkRestore(
            $validated['ids'],
            $request->user(),
            fn (Report $report) => $this->authorize('restore', $report)
        );

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()->route('reports.index');
    }

    /**
     * Import reports from an uploaded CSV file.
     *
     * Authorisation is handled by ImportReportRequest::authorize().
     */
    public function import(ImportReportRequest $request): JsonResponse|RedirectResponse
    {
        $result = $this->management->import($request);

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()->route('reports.index')->with('import_result', $result);
    }

    /**
     * Export reports matching the current filters as a CSV download.
     *
     * Authorises via the 'export' policy before proceeding.
     */
    public function export(Request $request): JsonResponse|RedirectResponse|StreamedResponse
    {
        $this->authorize('export', Report::class);

        return $this->management->export(
            $request->only([
                'search', 
                'type', 
                'format', 
                'trashed'
                ])
                );
    }

    /**
     * Generate the report's output file on demand ("run now").
     *
     * Authorises via the 'export' policy before proceeding.
     */
    public function run(Request $request, Report $report): JsonResponse
    {
        $this->authorize('export', Report::class);

        $path = $this->management->run($report, $request->user());

        return response()->json(['path' => $path]);
    }
}
