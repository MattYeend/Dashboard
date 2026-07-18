<?php

namespace App\Http\Controllers;

use App\Http\Requests\Categories\StoreCategoryRequest;
use App\Http\Requests\Categories\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\Categories\ManagementService;
use App\Services\Categories\QueryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
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
     * Passes paginated categories to the Orders/Index Inertia page.
     *
     * Authorises via the 'viewAny' policy before returning data.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Category::class);

        $data = $this->query->getPaginated(
            $request->user(),
            $request->only(['search', 'sort_by', 'sort_direction', 'trashed', 'per_page'])
        );

        return Inertia::render('Categories/Index', $data);
    }

    /**
     * Show the form for creating a new category.
     *
     * Authorises via the 'create' policy before rendering.
     */
    public function create(): Response
    {
        $this->authorize('create', Category::class);

        return Inertia::render('Categories/Create', $this->query->getFormData());
    }

    /**
     * Store a newly created resource in storage.
     *
     * Validation is handled upstream by StoreIndustryRequest.
     *
     * After storing, an audit log entry is written against the
     * authenticated user.
     */
    public function store(StoreCategoryRequest $request): JsonResponse|RedirectResponse
    {
        $category = $this->management->store($request);

        if ($request->wantsJson()) {
            return response()->json($category, 201);
        }

        return redirect()->route('categories.show', $category->id);
    }

    /**
     * Display the specified resource.
     *
     * Passes a single category to the Categories/Show Inertia page.
     *
     * Authorises via the 'view' and 'access' policies before rendering.
     */
    public function show(
        Category $category,
        Request $request
    ): Response {
        $this->authorize('view', $category);

        $data = $this->query->getById(
            $request->user(),
            $category->id
        );

        return Inertia::render('Categories/Show', $data);
    }

    /**
     * Show the form for editing an existing category.
     *
     * Authorises via the 'update' policy before rendering.
     */
    public function edit(
        Category $category,
        Request $request
    ): Response {
        $this->authorize('update', $category);

        $data = array_merge(
           $this->query->getById($request->user(), $category->id),
           $this->query->getFormData($category->id),
       );

        return Inertia::render('Categories/Edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * Validation is handled upstream by UpdateIndustryRequest, which also
     * implicitly authorises the operation via its authorize() method.
     *
     * After updating, an audit log entry is written against the authenticated
     * user.
     */
    public function update(
        UpdateCategoryRequest $request,
        Category $category
    ): JsonResponse|RedirectResponse {
        $category = $this->management->update($request, $category);

        if ($request->wantsJson()) {
            return response()->json($category);
        }

        return redirect()->route('categories.show', $category->id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * Authorises via the 'delete' policy before proceeding.
     *
     * The audit log entry is written before the deletion so that the
     * category instance is still fully accessible during logging.
     */
    public function destroy(
        Request $request,
        Category $category
    ): JsonResponse|RedirectResponse {
        $this->authorize('delete', $category);

        $this->management->destroy($category, $request->user());

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('categories.index');
    }

    /**
     * Restore a soft-deleted category.
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
        $category = Category::onlyTrashed()->findOrFail($id);

        $this->authorize('restore', $category);

        $this->management->restore($id, $request->user());

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('categories.index');
    }

    /**
     * Permanently delete a soft-deleted category.
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
        $category = Category::onlyTrashed()->findOrFail($id);

        $this->authorize('forceDelete', $category);

        $this->management->forceDelete($id, $request->user());

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('categories.index');
    }

    /**
     * Bulk soft-delete multiple categories.
     *
     * Authorises each category individually via the 'delete' policy.
     */
    public function bulkDelete(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['required', 'integer', 'exists:categories,id'],
        ]);

        $actor = $request->user();
        $ids = $request->input('ids');

        $this->management->bulkDelete(
            $ids,
            $actor,
            fn (Category $category) => $this->authorize('delete', $category)
        );

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('categories.index');
    }

    /**
     * Bulk restore multiple soft-deleted categories.
     *
     * Authorises each category individually via the 'restore' policy.
     */
    public function bulkRestore(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['required', 'integer', 'exists:categories,id'],
        ]);

        $this->management->bulkRestore(
            $validated['ids'],
            $request->user(),
            fn (Category $category) => $this->authorize('restore', $category)
        );

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('categories.index');
    }
}
