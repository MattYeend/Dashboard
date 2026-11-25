<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\BulkIdsRequest;
use App\Http\Requests\EmailSuppressions\ImportEmailSuppressionRequest;
use App\Http\Requests\EmailSuppressions\StoreEmailSuppressionRequest;
use App\Http\Requests\EmailSuppressions\UpdateEmailSuppressionRequest;
use App\Models\EmailSuppression;
use App\Services\EmailSuppressions\ManagementService;
use App\Services\EmailSuppressions\QueryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmailSuppressionController extends Controller
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
     * Authorises via the 'viewAny' policy before returning data.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', EmailSuppression::class);

        $data = $this->query->getPaginated(
            $request->user(),
            $request->only([
                'search',
                'reason',
                'sort_by',
                'sort_direction',
                'trashed',
                'per_page',
            ])
        );

        return Inertia::render('EmailSuppressions/Index', $data);
    }

    /**
     * Show the form for creating a new email suppression.
     *
     * Authorises via the 'create' policy before rendering.
     */
    public function create(): Response
    {
        $this->authorize('create', EmailSuppression::class);

        return Inertia::render(
            'EmailSuppressions/Create',
            $this->query->getFormData()
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * Validation and authorisation are handled upstream by
     * StoreEmailSuppressionRequest.
     */
    public function store(StoreEmailSuppressionRequest $request): JsonResponse|RedirectResponse
    {
        $emailSuppression = $this->management->store($request);

        if ($request->wantsJson()) {
            return response()->json($emailSuppression, 201);
        }

        return redirect()
            ->route('email-suppressions.show', $emailSuppression->id)
            ->with('success', 'Email suppression created.');
    }

    /**
     * Display the specified resource.
     *
     * Authorises via the 'view' policy before rendering.
     */
    public function show(
        EmailSuppression $emailSuppression,
        Request $request
    ): Response {
        $this->authorize('view', $emailSuppression);

        $data = array_merge(
            $this->query->getById(
                $request->user(),
                $emailSuppression
            ),
            ['canEdit' => $request->user()->can('update', $emailSuppression)],
        );

        return Inertia::render('EmailSuppressions/Show', $data);
    }

    /**
     * Show the form for editing an existing email suppression.
     *
     * Authorises via the 'update' policy before rendering.
     */
    public function edit(
        EmailSuppression $emailSuppression,
        Request $request
    ): Response {
        $this->authorize('update', $emailSuppression);

        $data = array_merge(
            $this->query->getById(
                $request->user(),
                $emailSuppression
            ),
            $this->query->getFormData(),
        );

        return Inertia::render('EmailSuppressions/Edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * Validation and authorisation are handled upstream by
     * UpdateEmailSuppressionRequest.
     */
    public function update(
        UpdateEmailSuppressionRequest $request,
        EmailSuppression $emailSuppression
    ): JsonResponse|RedirectResponse {
        $emailSuppression = $this->management->update($request, $emailSuppression);

        if ($request->wantsJson()) {
            return response()->json($emailSuppression);
        }

        return redirect()
            ->route('email-suppressions.show', $emailSuppression->id)
            ->with('success', 'Email suppression updated.');
    }

    /**
     * Soft delete an email suppression.
     *
     * Authorises via the 'delete' policy (Admin only) before proceeding.
     * This is a deliberate re-subscription action and is audit logged.
     */
    public function destroy(
        Request $request,
        EmailSuppression $emailSuppression
    ): JsonResponse|RedirectResponse {
        $this->authorize('delete', $emailSuppression);

        $this->management->destroy($emailSuppression, $request->user());

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()
            ->route('email-suppressions.index')
            ->with('success', 'Email suppression deleted.');
    }

    /**
     * Restore a soft-deleted email suppression.
     *
     * Resolves the trashed model manually since route model binding
     * excludes soft-deleted records by default.
     */
    public function restore(
        int $id,
        Request $request
    ): JsonResponse|RedirectResponse {
        $emailSuppression = EmailSuppression::onlyTrashed()->findOrFail($id);

        $this->authorize('restore', $emailSuppression);

        $this->management->restore($id, $request->user());

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()
            ->route('email-suppressions.index')
            ->with('success', 'Email suppression restored.');
    }

    /**
     * Permanently delete a soft-deleted email suppression.
     *
     * Only records that are already soft-deleted can be force deleted.
     */
    public function forceDelete(
        int $id,
        Request $request
    ): JsonResponse|RedirectResponse {
        $emailSuppression = EmailSuppression::onlyTrashed()->findOrFail($id);

        $this->authorize('forceDelete', $emailSuppression);

        $this->management->forceDelete($id, $request->user());

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()
            ->route('email-suppressions.index')
            ->with('success', 'Email suppression permanently deleted.');
    }

    /**
     * Bulk soft-delete multiple email suppressions.
     *
     * Authorises each email suppression individually via the 'delete' policy.
     */
    public function bulkDelete(BulkIdsRequest $request): JsonResponse|RedirectResponse
    {
        $this->authorize('bulkDelete', EmailSuppression::class);

        $result = $this->management->bulkDelete(
            $request->validated('ids'),
            $request->user(),
            fn (EmailSuppression $emailSuppression) => $this->authorize('delete', $emailSuppression)
        );

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()
            ->route('email-suppressions.index')
            ->with('success', count($result['deleted']).' email suppressions deleted.');
    }

    /**
     * Bulk restore multiple soft-deleted email suppressions.
     *
     * Authorises each email suppression individually via the 'restore' policy.
     */
    public function bulkRestore(BulkIdsRequest $request): JsonResponse|RedirectResponse
    {
        $this->authorize('bulkRestore', EmailSuppression::class);

        $result = $this->management->bulkRestore(
            $request->validated('ids'),
            $request->user(),
            fn (EmailSuppression $emailSuppression) => $this->authorize('restore', $emailSuppression)
        );

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()
            ->route('email-suppressions.index')
            ->with('success', count($result['restored']).' email suppressions restored.');
    }

    /**
     * Import email suppressions from an uploaded CSV file.
     *
     * Authorisation is handled by ImportEmailSuppressionRequest::authorize().
     */
    public function import(ImportEmailSuppressionRequest $request): JsonResponse|RedirectResponse
    {
        $result = $this->management->import($request);

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()
            ->route('email-suppressions.index')
            ->with('import_result', $result);
    }

    /**
     * Export email suppressions matching the current filters as a CSV download.
     *
     * Authorises via the 'export' policy before proceeding.
     */
    public function export(Request $request): JsonResponse|RedirectResponse|StreamedResponse
    {
        $this->authorize('export', EmailSuppression::class);

        return $this->management->export(
            $request->only(['search', 'reason', 'trashed'])
        );
    }

    /**
     * Parse and validate an uploaded CSV, returning a per-row preview
     * without persisting anything.
     *
     * Authorisation is handled by ImportEmailSuppressionRequest::authorize().
     */
    public function importPreview(ImportEmailSuppressionRequest $request): JsonResponse
    {
        $importer = $this->management->importer();
        $token = $importer->storeUpload($request->file('file'), $request->user()->id);

        return response()->json(['token' => $token, ...$importer->preview($token, $request->user()->id)]);
    }

    /**
     * Commit a previously previewed import, persisting valid rows only.
     *
     * Authorises via the 'import' policy before proceeding.
     */
    public function importCommit(Request $request): JsonResponse
    {
        $this->authorize('import', EmailSuppression::class);

        $request->validate(['token' => ['required', 'uuid']]);

        try {
            $result = $this->management->importer()->commit(
                $request->input('token'),
                $request->user()->id
            );
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($result);
    }
}