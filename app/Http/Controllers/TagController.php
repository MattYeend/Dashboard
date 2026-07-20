<?php

namespace App\Http\Controllers;

use App\Http\Requests\Tags\StoreTagRequest;
use App\Http\Requests\Tags\UpdateTagRequest;
use App\Models\Tag;
use App\Services\Tags\ManagementService;
use App\Services\Tags\QueryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TagController extends Controller
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
     * Passes paginated tags to the Tags/Index Inertia page.
     *
     * Authorises via the 'viewAny' policy before returning data.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Tag::class);

        $data = $this->query->getPaginated(
            $request->user(),
            $request->only(['search', 'sort_by', 'sort_direction', 'trashed', 'per_page'])
        );

        return Inertia::render('Tags/Index', $data);
    }

    /**
     * Show the form for creating a new tag.
     *
     * Authorises via the 'create' policy before rendering.
     */
    public function create(): Response
    {
        $this->authorize('create', Tag::class);

        return Inertia::render('Tags/Create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * Validation is handled upstream by StoreTagRequest.
     *
     * After storing, an audit log entry is written against the
     * authenticated user.
     */
    public function store(StoreTagRequest $request): JsonResponse|RedirectResponse
    {
        $tag = $this->management->store($request);

        if ($request->wantsJson()) {
            return response()->json($tag, 201);
        }

        return redirect()->route('tags.show', $tag->id);
    }

    /**
     * Display the specified resource.
     *
     * Passes a single tag to the Tags/Show Inertia page.
     *
     * Authorises via the 'view' policy before rendering.
     */
    public function show(
        Tag $tag,
        Request $request
    ): Response {
        $this->authorize('view', $tag);

        $data = $this->query->getById(
            $request->user(),
            $tag->id
        );

        return Inertia::render('Tags/Show', $data);
    }

    /**
     * Show the form for editing an existing tag.
     *
     * Authorises via the 'update' policy before rendering.
     */
    public function edit(
        Tag $tag,
        Request $request
    ): Response {
        $this->authorize('update', $tag);

        $data = $this->query->getById(
            $request->user(),
            $tag->id
        );

        return Inertia::render('Tags/Edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * Validation is handled upstream by UpdateTagRequest, which also
     * implicitly authorises the operation via its authorize() method.
     *
     * After updating, an audit log entry is written against the authenticated
     * user.
     */
    public function update(
        UpdateTagRequest $request,
        Tag $tag
    ): JsonResponse|RedirectResponse {
        $tag = $this->management->update($request, $tag);

        if ($request->wantsJson()) {
            return response()->json($tag);
        }

        return redirect()->route('tags.show', $tag->id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * Authorises via the 'delete' policy before proceeding.
     *
     * The audit log entry is written before the deletion so that the
     * tag instance is still fully accessible during logging.
     */
    public function destroy(
        Request $request,
        Tag $tag
    ): JsonResponse|RedirectResponse {
        $this->authorize('delete', $tag);

        $this->management->destroy($tag, $request->user());

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('tags.index');
    }

    /**
     * Restore a soft-deleted tag.
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
        $tag = Tag::onlyTrashed()->findOrFail($id);

        $this->authorize('restore', $tag);

        $this->management->restore($id, $request->user());

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('tags.index');
    }

    /**
     * Permanently delete a soft-deleted tag.
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
        $tag = Tag::onlyTrashed()->findOrFail($id);

        $this->authorize('forceDelete', $tag);

        $this->management->forceDelete($id, $request->user());

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('tags.index');
    }

    /**
     * Bulk soft-delete multiple tags.
     *
     * Authorises each tag individually via the 'delete' policy.
     */
    public function bulkDelete(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['required', 'integer', 'exists:tags,id'],
        ]);

        $actor = $request->user();
        $ids = $request->input('ids');

        $this->management->bulkDelete(
            $ids,
            $actor,
            fn (Tag $tag) => $this->authorize('delete', $tag)
        );

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('tags.index');
    }

    /**
     * Bulk restore multiple soft-deleted tags.
     *
     * Authorises each tag individually via the 'restore' policy.
     */
    public function bulkRestore(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['required', 'integer', 'exists:tags,id'],
        ]);

        $this->management->bulkRestore(
            $validated['ids'],
            $request->user(),
            fn (Tag $tag) => $this->authorize('restore', $tag)
        );

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('tags.index');
    }
}
