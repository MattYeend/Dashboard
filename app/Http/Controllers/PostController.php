<?php

namespace App\Http\Controllers;

use App\Http\Requests\Posts\StorePostRequest;
use App\Http\Requests\Posts\UpdatePostRequest;
use App\Models\Post;
use App\Services\Posts\ManagementService;
use App\Services\Posts\QueryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
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
     * Passes paginated posts to the Orders/Index Inertia page.
     *
     * Authorises via the 'viewAny' policy before returning data.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Post::class);

        $data = $this->query->getPaginated(
            $request->user(),
            $request->only(['search', 'sort_by', 'sort_direction', 'trashed', 'per_page'])
        );

        return Inertia::render('Posts/Index', $data);
    }

    /**
     * Show the form for creating a new post.
     *
     * Authorises via the 'create' policy before rendering.
     */
    public function create(): Response
    {
        $this->authorize('create', Post::class);

        return Inertia::render('Posts/Create', $this->query->getFormData());
    }

    /**
     * Store a newly created resource in storage.
     *
     * Validation is handled upstream by StorePostRequest.
     *
     * After storing, an audit log entry is written against the
     * authenticated user.
     */
    public function store(StorePostRequest $request): JsonResponse|RedirectResponse
    {
        $post = $this->management->store($request);

        if ($request->wantsJson()) {
            return response()->json($post, 201);
        }

        return redirect()->route('posts.show', $post->id);
    }

    /**
     * Display the specified resource.
     *
     * Passes a single post to the Posts/Show Inertia page.
     *
     * Authorises via the 'view' and 'access' policies before rendering.
     */
    public function show(
        Post $post,
        Request $request
    ): Response {
        $this->authorize('view', $post);

        $data = $this->query->getById(
            $request->user(),
            $post->id
        );

        return Inertia::render('Posts/Show', $data);
    }

    /**
     * Show the form for editing an existing post.
     *
     * Authorises via the 'update' policy before rendering.
     */
    public function edit(
        Post $post,
        Request $request
    ): Response {
        $this->authorize('update', $post);

        $data = $this->query->getById(
            $request->user(),
            $post->id
        );

        return Inertia::render('Posts/Edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * Validation is handled upstream by UpdatePostRequest, which also
     * implicitly authorises the operation via its authorize() method.
     *
     * After updating, an audit log entry is written against the authenticated
     * user.
     */
    public function update(
        UpdatePostRequest $request,
        Post $post
    ): JsonResponse|RedirectResponse {
        $post = $this->management->update($request, $post);

        if ($request->wantsJson()) {
            return response()->json($post);
        }

        return redirect()->route('posts.show', $post->id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * Authorises via the 'delete' policy before proceeding.
     *
     * The audit log entry is written before the deletion so that the
     * post instance is still fully accessible during logging.
     */
    public function destroy(
        Request $request,
        Post $post
    ): JsonResponse|RedirectResponse {
        $this->authorize('delete', $post);

        $this->management->destroy($post, $request->user());

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('posts.index');
    }

    /**
     * Restore a soft-deleted post.
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
        $post = Post::onlyTrashed()->findOrFail($id);

        $this->authorize('restore', $post);

        $this->management->restore($id, $request->user());

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('posts.index');
    }

    /**
     * Permanently delete a soft-deleted post.
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
        $post = Post::onlyTrashed()->findOrFail($id);

        $this->authorize('forceDelete', $post);

        $this->management->forceDelete($id, $request->user());

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('posts.index');
    }

    /**
     * Bulk soft-delete multiple posts.
     *
     * Authorises each post individually via the 'delete' policy.
     */
    public function bulkDelete(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['required', 'integer', 'exists:posts,id'],
        ]);

        $actor = $request->user();
        $ids = $request->input('ids');

        $this->management->bulkDelete(
            $ids,
            $actor,
            fn (Post $post) => $this->authorize('delete', $post)
        );

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('posts.index');
    }

    /**
     * Bulk restore multiple soft-deleted posts.
     *
     * Authorises each post individually via the 'restore' policy.
     */
    public function bulkRestore(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['required', 'integer', 'exists:posts,id'],
        ]);

        $this->management->bulkRestore(
            $validated['ids'],
            $request->user(),
            fn (Post $post) => $this->authorize('restore', $post)
        );

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('posts.index');
    }
}
