<?php

namespace App\Http\Controllers;

use App\Http\Requests\Comments\ImportCommentRequest;
use App\Http\Requests\Comments\StoreCommentRequest;
use App\Http\Requests\Comments\UpdateCommentRequest;
use App\Models\Comment;
use App\Models\Post;
use App\Services\Comments\ManagementService;
use App\Services\Comments\QueryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CommentController extends Controller
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
     * Display a paginated listing of comments for the given post.
     *
     * Authorises via the 'viewAny' policy before returning data.
     */
    public function index(Post $post, Request $request): Response
    {
        $this->authorize('viewAny', Comment::class);

        $data = $this->query->getPaginated(
            $request->user(),
            $post,
            $request->only(['search', 'sort_by', 'sort_direction', 'trashed', 'per_page'])
        );

        return Inertia::render('Posts/Comments/Index', $data);
    }

    /**
     * Display the specified comment.
     *
     * Authorises via the 'view' policy before rendering.
     */
    public function show(Post $post, Comment $comment): Response
    {
        $this->authorize('view', $comment);

        $data = $this->query->getById($post, $comment->id);

        return Inertia::render('Posts/Comments/Show', $data);
    }

    /**
     * Store a newly created comment on the given post.
     *
     * Authorisation is handled upstream by StoreCommentRequest.
     */
    public function store(
        StoreCommentRequest $request,
        Post $post
    ): JsonResponse|RedirectResponse {
        $comment = $this->management->store($request, $post);

        if ($request->wantsJson()) {
            return response()->json($comment, 201);
        }

        return back();
    }

    /**
     * Update the specified comment in storage.
     *
     * Authorisation is handled upstream by UpdateCommentRequest.
     */
    public function update(
        UpdateCommentRequest $request,
        Post $post,
        Comment $comment
    ): JsonResponse|RedirectResponse {
        $comment = $this->management->update($request, $comment);

        if ($request->wantsJson()) {
            return response()->json($comment);
        }

        return back();
    }

    /**
     * Remove the specified comment from storage.
     *
     * Authorises via the 'delete' policy before proceeding.
     */
    public function destroy(
        Post $post,
        Comment $comment,
        Request $request
    ): JsonResponse|RedirectResponse {
        $this->authorize('delete', $comment);

        $this->management->destroy($comment, $request->user());

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return back();
    }

    /**
     * Restore a soft-deleted comment.
     *
     * Resolves the trashed model manually since route model binding
     * excludes soft-deleted records by default.
     *
     * Authorises via the 'restore' policy before proceeding.
     */
    public function restore(
        Post $post,
        int $id,
        Request $request
    ): JsonResponse|RedirectResponse {
        $comment = Comment::onlyTrashed()->where('post_id', $post->id)->findOrFail($id);

        $this->authorize('restore', $comment);

        $this->management->restore($id, $request->user());

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return back();
    }

    /**
     * Permanently delete a soft-deleted comment.
     *
     * Authorises via the 'forceDelete' policy before proceeding.
     */
    public function forceDelete(
        Post $post,
        int $id,
        Request $request
    ): JsonResponse|RedirectResponse {
        $comment = Comment::onlyTrashed()->where('post_id', $post->id)->findOrFail($id);

        $this->authorize('forceDelete', $comment);

        $this->management->forceDelete($id, $request->user());

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return back();
    }

    /**
     * Bulk soft-delete multiple comments belonging to the given post.
     *
     * Authorises each comment individually via the 'delete' policy.
     */
    public function bulkDelete(Post $post, Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['required', 'integer', 'exists:comments,id'],
        ]);

        $result = $this->management->bulkDelete(
            $request->input('ids'),
            $request->user(),
            fn (Comment $comment) => $this->authorize('delete', $comment)
        );

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return back();
    }

    /**
     * Bulk restore multiple soft-deleted comments belonging to the given post.
     *
     * Authorises each comment individually via the 'restore' policy.
     */
    public function bulkRestore(Post $post, Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['required', 'integer', 'exists:comments,id'],
        ]);

        $result = $this->management->bulkRestore(
            $validated['ids'],
            $request->user(),
            fn (Comment $comment) => $this->authorize('restore', $comment)
        );

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return back();
    }

    /**
     * Import comments from an uploaded CSV file, scoped to the given post.
     *
     * Authorisation is handled by ImportCommentRequest::authorize().
     */
    public function import(ImportCommentRequest $request, Post $post): JsonResponse|RedirectResponse
    {
        $result = $this->management->import($request, $post);

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return back()->with('import_result', $result);
    }

    /**
     * Export a post's comments matching the current filters as a CSV download.
     *
     * Authorises via the 'export' policy before proceeding.
     */
    public function export(Post $post, Request $request): JsonResponse|RedirectResponse|StreamedResponse
    {
        $this->authorize('export', Comment::class);

        return $this->management->export(
            $post,
            $request->only(['search', 'trashed'])
        );
    }

    /**
     * Like the given comment for the currently authenticated user.
     *
     * Authorises via the 'view' policy on the parent post, since
     * liking a comment requires being able to see the post it's on.
     */
    public function like(
        Post $post,
        Comment $comment,
        Request $request
    ): RedirectResponse {
        $this->authorize('view', $post);

        $this->management->like($comment, $request->user());

        return back();
    }

    /**
     * Unlike the given comment for the currently authenticated user.
     */
    public function unlike(
        Post $post,
        Comment $comment,
        Request $request
    ): RedirectResponse {
        $this->authorize('view', $post);

        $this->management->unlike($comment, $request->user());

        return back();
    }
}
