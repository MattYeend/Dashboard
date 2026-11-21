<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\BulkIdsRequest;
use App\Http\Requests\EmailTemplates\ImportEmailTemplateRequest;
use App\Http\Requests\EmailTemplates\StoreEmailTemplateRequest;
use App\Http\Requests\EmailTemplates\UpdateEmailTemplateRequest;
use App\Models\EmailTemplate;
use App\Services\EmailTemplates\ManagementService;
use App\Services\EmailTemplates\QueryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmailTemplateController extends Controller
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
        $this->authorize('viewAny', EmailTemplate::class);

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

        return Inertia::render('EmailTemplates/Index', $data);
    }

    /**
     * Show the form for creating a new email template.
     *
     * Authorises via the 'create' policy before rendering.
     */
    public function create(Request $request): Response
    {
        $this->authorize('create', EmailTemplate::class);

        return Inertia::render(
            'EmailTemplates/Create',
            $this->query->getFormData($request->user())
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * Validation and authorisation are handled upstream by
     * StoreEmailTemplateRequest.
     */
    public function store(StoreEmailTemplateRequest $request): JsonResponse|RedirectResponse
    {
        $emailTemplate = $this->management->store($request);

        if ($request->wantsJson()) {
            return response()->json($emailTemplate, 201);
        }

        return redirect()
            ->route('email-templates.show', $emailTemplate->id)
            ->with('success', 'Email template created.');
    }

    /**
     * Display the specified resource.
     *
     * Authorises via the 'view' policy before rendering.
     */
    public function show(
        EmailTemplate $emailTemplate,
        Request $request
    ): Response {
        $this->authorize('view', $emailTemplate);

        $data = array_merge(
            $this->query->getById(
                $request->user(),
                $emailTemplate
            ),
            ['canEdit' => $request->user()->can('update', $emailTemplate)],
        );

        return Inertia::render('EmailTemplates/Show', $data);
    }

    /**
     * Show the form for editing an existing email template.
     *
     * Authorises via the 'update' policy before rendering.
     */
    public function edit(
        EmailTemplate $emailTemplate,
        Request $request
    ): Response {
        $this->authorize('update', $emailTemplate);

        $data = array_merge(
            $this->query->getById(
                $request->user(),
                $emailTemplate
            ),
            $this->query->getFormData($request->user()),
        );

        return Inertia::render('EmailTemplates/Edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * Validation and authorisation are handled upstream by
     * UpdateEmailTemplateRequest.
     */
    public function update(
        UpdateEmailTemplateRequest $request,
        EmailTemplate $emailTemplate
    ): JsonResponse|RedirectResponse {
        $emailTemplate = $this->management->update($request, $emailTemplate);

        if ($request->wantsJson()) {
            return response()->json($emailTemplate);
        }

        return redirect()
            ->route('email-templates.show', $emailTemplate->id)
            ->with('success', 'Email template updated.');
    }

    /**
     * Soft delete an email template.
     *
     * Authorises via the 'delete' policy before proceeding.
     */
    public function destroy(
        Request $request,
        EmailTemplate $emailTemplate
    ): JsonResponse|RedirectResponse {
        $this->authorize('delete', $emailTemplate);

        $this->management->destroy($emailTemplate, $request->user());

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()
            ->route('email-templates.index')
            ->with('success', 'Email template deleted.');
    }

    /**
     * Restore a soft-deleted email template.
     *
     * Resolves the trashed model manually since route model binding
     * excludes soft-deleted records by default.
     */
    public function restore(
        int $id,
        Request $request
    ): JsonResponse|RedirectResponse {
        $emailTemplate = EmailTemplate::onlyTrashed()->findOrFail($id);

        $this->authorize('restore', $emailTemplate);

        $this->management->restore($id, $request->user());

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()
            ->route('email-templates.index')
            ->with('success', 'Email template restored.');
    }

    /**
     * Permanently delete a soft-deleted email template.
     *
     * Only records that are already soft-deleted can be force deleted.
     */
    public function forceDelete(
        int $id,
        Request $request
    ): JsonResponse|RedirectResponse {
        $emailTemplate = EmailTemplate::onlyTrashed()->findOrFail($id);

        $this->authorize('forceDelete', $emailTemplate);

        $this->management->forceDelete($id, $request->user());

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()
            ->route('email-templates.index')
            ->with('success', 'Email template permanently deleted.');
    }

    /**
     * Bulk soft-delete multiple email templates.
     *
     * Authorises each email template individually via the 'delete' policy.
     */
    public function bulkDelete(BulkIdsRequest $request): JsonResponse|RedirectResponse
    {
        $this->authorize('bulkDelete', EmailTemplate::class);

        $result = $this->management->bulkDelete(
            $request->validated('ids'),
            $request->user(),
            fn (EmailTemplate $emailTemplate) => $this->authorize('delete', $emailTemplate)
        );

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()
            ->route('email-templates.index')
            ->with('success', count($result['deleted']).' email templates deleted.');
    }

    /**
     * Bulk restore multiple soft-deleted email templates.
     *
     * Authorises each email template individually via the 'restore' policy.
     */
    public function bulkRestore(BulkIdsRequest $request): JsonResponse|RedirectResponse
    {
        $this->authorize('bulkRestore', EmailTemplate::class);

        $result = $this->management->bulkRestore(
            $request->validated('ids'),
            $request->user(),
            fn (EmailTemplate $emailTemplate) => $this->authorize('restore', $emailTemplate)
        );

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()
            ->route('email-templates.index')
            ->with('success', count($result['restored']).' email templates restored.');
    }

    /**
     * Import email templates from an uploaded CSV file.
     *
     * Authorisation is handled by ImportEmailTemplateRequest::authorize().
     */
    public function import(ImportEmailTemplateRequest $request): JsonResponse|RedirectResponse
    {
        $result = $this->management->import($request);

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return redirect()
            ->route('email-templates.index')
            ->with('import_result', $result);
    }

    /**
     * Export email templates matching the current filters as a CSV download.
     *
     * Authorises via the 'export' policy before proceeding.
     */
    public function export(Request $request): JsonResponse|RedirectResponse|StreamedResponse
    {
        $this->authorize('export', EmailTemplate::class);

        return $this->management->export(
            $request->only(['search', 'trashed'])
        );
    }

    /**
     * Parse and validate an uploaded CSV, returning a per-row preview
     * without persisting anything.
     *
     * Authorisation is handled by ImportEmailTemplateRequest::authorize().
     */
    public function importPreview(ImportEmailTemplateRequest $request): JsonResponse
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
        $this->authorize('import', EmailTemplate::class);

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
