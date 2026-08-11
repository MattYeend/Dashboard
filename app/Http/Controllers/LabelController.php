<?php

namespace App\Http\Controllers;

use App\Http\Requests\Labels\ImportLabelRequest;
use App\Http\Requests\Labels\StoreLabelRequest;
use App\Http\Requests\Labels\UpdateLabelRequest;
use App\Models\Label;
use App\Services\Labels\ManagementService;
use App\Services\Labels\QueryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LabelController extends Controller
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
     * Passes paginated labels to the Labels/Index Inertia page.
     *
     * Authorises via the 'viewAny' policy before returning data.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Label::class);

        $data = $this->query->getPaginated(
            $request->user(),
            $request->only([
                'search',
                'sort_by',
                'sort_direction',
                'trashed',
                'per_page',
            ])
        );

        return Inertia::render('Labels/Index', $data);
    }

    /**
     * Show the form for creating a new label.
     *
     * Authorises via the 'create' policy before rendering.
     */
    public function create(): Response
    {
        $this->authorize('create', Label::class);

        return Inertia::render('Labels/Create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * Validation is handled upstream by StoreLabelRequest.
     *
     * After storing, an audit log entry is written against the
     * authenticated user.
     */
    public function store(StoreLabelRequest $request): JsonResponse|RedirectResponse
    {
        $label = $this->management->store($request);

        if ($request->wantsJson()) {
            return response()->json($label, 201);
        }

        return redirect()->route('labels.show', $label->id);
    }

    /**
     * Display the specified resource.
     *
     * Passes a single label to the Labels/Show Inertia page.
     *
     * Authorises via the 'view' policy before rendering.
     */
    public function show(
        Label $label,
        Request $request
    ): Response {
        $this->authorize('view', $label);

        $data = $this->query->getById(
            $request->user(),
            $label->id
        );

        return Inertia::render('Labels/Show', $data);
    }

    /**
     * Show the form for editing an existing label.
     *
     * Authorises via the 'update' policy before rendering.
     */
    public function edit(
        Label $label,
        Request $request
    ): Response {
        $this->authorize('update', $label);

        $data = $this->query->getById(
            $request->user(),
            $label->id
        );

        return Inertia::render('Labels/Edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * Validation is handled upstream by UpdateLabelRequest, which also
     * implicitly authorises the operation via its authorize() method.
     *
     * After updating, an audit log entry is written against the authenticated
     * user.
     */
    public function update(
        UpdateLabelRequest $request,
        Label $label
    ): JsonResponse|RedirectResponse {
        $label = $this->management->update($request, $label);

        if ($request->wantsJson()) {
            return response()->json($label);
        }

        return redirect()->route('labels.show', $label->id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * Authorises via the 'delete' policy before proceeding.
     *
     * The audit log entry is written before the deletion so that the
     * label instance is still fully accessible during logging.
     */
    public function destroy(
        Request $request,
        Label $label
    ): JsonResponse|RedirectResponse {
        $this->authorize('delete', $label);

        $this->management->destroy($label, $request->user());

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('labels.index');
    }

    /**
     * Restore a soft-deleted label.
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
        $label = Label::onlyTrashed()->findOrFail($id);

        $this->authorize('restore', $label);

        $this->management->restore($id, $request->user());

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('labels.index');
    }

    /**
     * Permanently delete a soft-deleted label.
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
        $label = Label::onlyTrashed()->findOrFail($id);

        $this->authorize('forceDelete', $label);

        $this->management->forceDelete($id, $request->user());

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('labels.index');
    }

    /**
     * Bulk soft-delete multiple labels.
     *
     * Authorises each label individually via the 'delete' policy.
     */
    public function bulkDelete(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['required', 'integer'],
        ]);

        $actor = $request->user();
        $ids = $request->input('ids');

        $result = $this->management->bulkDelete(
            $ids,
            $actor,
            fn (Label $label) => $this->authorize('delete', $label)
        );

        if (request()->wantsJson()) {
            return response()->json($result);
        }

        return redirect()->route('labels.index');
    }

    /**
     * Bulk restore multiple soft-deleted labels.
     *
     * Authorises each label individually via the 'restore' policy.
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
            fn (Label $label) => $this->authorize('restore', $label)
        );

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()->route('labels.index');
    }

    /**
     * Import labels from an uploaded CSV file.
     *
     * Authorisation is handled by ImportLabelRequest::authorize().
     */
    public function import(ImportLabelRequest $request): JsonResponse|RedirectResponse
    {
        $result = $this->management->import($request);

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()
            ->route('labels.index')
            ->with('import_result', $result);
    }

    /**
     * Export labels matching the current filters as a CSV download.
     *
     * Authorises via the 'export' policy before proceeding.
     */
    public function export(Request $request): JsonResponse|RedirectResponse|StreamedResponse
    {
        $this->authorize('export', Label::class);

        return $this->management->export(
            $request->only(['search', 'trashed'])
        );
    }
}
