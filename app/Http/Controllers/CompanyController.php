<?php

namespace App\Http\Controllers;

use App\Http\Requests\Companies\StoreCompanyRequest;
use App\Http\Requests\Companies\UpdateCompanyRequest;
use App\Models\Company;
use App\Services\Companies\ManagementService;
use App\Services\Companies\QueryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CompanyController extends Controller
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
     * Passes paginated companies to the Companies/Index Inertia page.
     *
     * Authorises via the 'viewAny' policy before returning data.
     */
    public function index(
        Request $request
    ): Response {
        $this->authorize('viewAny', Company::class);

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

        return Inertia::render('Companies/Index', $data);
    }

    /**
     * Show the form for creating a new company.
     *
     * Authorises via the 'create' policy before rendering.
     */
    public function create(): Response
    {
        $this->authorize('create', Company::class);

        $data = $this->query->getFormData();

        return Inertia::render('Companies/Create', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * Validation is handled upstream by StoreCompanyRequest.
     *
     * After storing, an audit log entry is written against the
     * authenticated user.
     */
    public function store(
        StoreCompanyRequest $request
    ): JsonResponse|RedirectResponse {
        $company = $this->management->store($request);

        if ($request->wantsJson()) {
            return response()->json($company, 201);
        }

        return redirect()->route('companies.show', $company->id);
    }

    /**
     * Display the specified resource.
     *
     * Passes a single company to the Companies/Show Inertia page.
     *
     * Authorises via the 'view' and 'access' policies before rendering.
     */
    public function show(
        Company $company,
        Request $request
    ): Response {
        $this->authorize('view', $company);

        $data = $this->query->getById(
            $request->user(),
            $company->id
        );

        return Inertia::render('Companies/Show', $data);
    }

    /**
     * Show the form for editing an existing company.
     *
     * Authorises via the 'update' policy before rendering.
     */
    public function edit(
        Company $company,
        Request $request
    ): Response {
        $this->authorize('update', $company);

        $data = $this->query->getById(
            $request->user(),
            $company->id
        );

        return Inertia::render('Companies/Edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * Validation is handled upstream by UpdateCompanyRequest, which also
     * implicitly authorises the operation via its authorize() method.
     *
     * After updating, an audit log entry is written against the authenticated
     * user.
     */
    public function update(
        UpdateCompanyRequest $request,
        Company $company
    ): JsonResponse|RedirectResponse {
        $company = $this->management->update($request, $company);

        if ($request->wantsJson()) {
            return response()->json($company);
        }

        return redirect()->route('companies.show', $company->id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * Authorises via the 'delete' policy before proceeding.
     *
     * The audit log entry is written before the deletion so that the
     * company instance is still fully accessible during logging.
     */
    public function destroy(
        Request $request,
        Company $company
    ): JsonResponse|RedirectResponse {
        $this->authorize('delete', $company);

        $this->management->destroy($company, $request->user());

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('companies.index');
    }

    /**
     * Restore a soft-deleted company.
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
        $company = Company::onlyTrashed()->findOrFail($id);

        $this->authorize('restore', $company);

        $this->management->restore($id, $request->user());

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('companies.index');
    }

    /**
     * Permanently delete a soft-deleted company.
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
        $company = Company::onlyTrashed()->findOrFail($id);

        $this->authorize('forceDelete', $company);

        $this->management->forceDelete($id, $request->user());

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('companies.index');
    }

    /**
     * Bulk soft-delete multiple companies.
     *
     * Authorises each company individually via the 'delete' policy.
     */
    public function bulkDelete(
        Request $request
    ): JsonResponse|RedirectResponse {
        $request->validate([
            'ids' => [
                'required',
                'array',
            ],
            'ids.*' => [
                'required',
                'integer',
                'exists:companies,id',
            ],
        ]);

        $actor = $request->user();
        $ids = $request->input('ids');

        $this->management->bulkDelete(
            $ids,
            $actor,
            fn (Company $company) => $this->authorize('delete', $company)
        );

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('companies.index');
    }

    /**
     * Bulk restore multiple soft-deleted companies.
     *
     * Authorises each company individually via the 'restore' policy.
     */
    public function bulkRestore(
        Request $request
    ): JsonResponse|RedirectResponse {
        $validated = $request->validate([
            'ids' => [
                'required',
                'array',
            ],
            'ids.*' => [
                'required',
                'integer',
                'exists:companies,id',
            ],
        ]);

        $this->management->bulkRestore(
            $validated['ids'],
            $request->user(),
            fn (Company $company) => $this->authorize('restore', $company)
        );

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('companies.index');
    }
}
