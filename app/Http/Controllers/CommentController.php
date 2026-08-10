<?php

namespace App\Http\Controllers;

use App\Http\Requests\Comments\ImportCommentRequest;
use App\Http\Requests\Comments\StoreCommentRequest;
use App\Http\Requests\Comments\UpdateCommentRequest;
use App\Models\Comment;
use App\Services\Comments\CommentableTypeRegistryService;
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
        protected readonly CommentableTypeRegistryService $registry,
    ) {}

    /**
     * Display a paginated listing of comments.
     *
     * Authorises via the 'viewAny' policy before returning data.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Comment::class);

        $data = $this->query->getPaginated(
            $request->user(),
            $request->only([
                'search', 'sort_by', 'sort_direction', 'trashed',
                'per_page', 'commentable_type', 'commentable_id',
            ])
        );

        return Inertia::render('Comments/Index', $data);
    }

    /**
     * Display the specified comment.
     *
     * Authorises via the 'view' policy before rendering.
     */
    public function show(
        Comment $comment,
        Request $request
    ): Response {
        $this->authorize('view', $comment);

        $data = $this->query->getById(
            $request->user(),
            $comment->id
        );

        return Inertia::render('Comments/Show', $data);
    }

    /**
     * Store a newly created comment.
     *
     * Authorisation is handled upstream by StoreCommentRequest.
     */
    public function store(StoreCommentRequest $request): JsonResponse|RedirectResponse
    {
        $comment = $this->management->store($request);

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
    public function update(UpdateCommentRequest $request, Comment $comment): JsonResponse|RedirectResponse
    {
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
    public function destroy(Comment $comment, Request $request): JsonResponse|RedirectResponse
    {
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
    public function restore(int $id, Request $request): JsonResponse|RedirectResponse
    {
        $comment = Comment::onlyTrashed()->findOrFail($id);

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
    public function forceDelete(int $id, Request $request): JsonResponse|RedirectResponse
    {
        $comment = Comment::onlyTrashed()->findOrFail($id);

        $this->authorize('forceDelete', $comment);

        $this->management->forceDelete($id, $request->user());

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return back();
    }

    /**
     * Bulk soft-delete multiple comments.
     *
     * Authorises each comment individually via the 'delete' policy.
     */
    public function bulkDelete(Request $request): JsonResponse|RedirectResponse
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
     * Bulk restore multiple soft-deleted comments.
     *
     * Authorises each comment individually via the 'restore' policy.
     */
    public function bulkRestore(Request $request): JsonResponse|RedirectResponse
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
     * Import comments from an uploaded CSV file.
     *
     * Authorisation is handled by ImportCommentRequest::authorize().
     */
    public function import(ImportCommentRequest $request): JsonResponse|RedirectResponse
    {
        $result = $this->management->import($request);

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return back()->with('import_result', $result);
    }

    /**
     * Export comments matching the current filters as a CSV download.
     *
     * Authorises via the 'export' policy before proceeding.
     */
    public function export(Request $request): JsonResponse|RedirectResponse|StreamedResponse
    {
        $this->authorize('export', Comment::class);

        return $this->management->export(
            $request->only(['search', 'trashed', 'commentable_type', 'commentable_id'])
        );
    }

    /**
     * Like the given comment for the currently authenticated user.
     *
     * Authorises via the 'view' policy on the comment itself, since
     * liking a comment requires being able to see it.
     */
    public function like(Comment $comment, Request $request): RedirectResponse
    {
        $this->authorize('view', $comment);

        $this->management->like($comment, $request->user());

        return back();
    }

    /**
     * Unlike the given comment for the currently authenticated user.
     *
     * Authorises via the 'view' policy on the comment itself, since
     * unliking a comment requires being able to see it.
     */
    public function unlike(Comment $comment, Request $request): RedirectResponse
    {
        $this->authorize('view', $comment);

        $this->management->unlike($comment, $request->user());

        return back();
    }

    /**
     * Get the list of selectable "owner" options for a given commentable type.
     */
    public function commentableOptions(Request $request): JsonResponse
    {
        $type = $request->query('type', '');

        $options = $this->query->getCommentableOptions($type);

        return response()->json($options);
    }
}
