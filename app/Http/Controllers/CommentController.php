<?php

namespace App\Http\Controllers;

use App\Http\Requests\Comments\StoreCommentRequest;
use App\Models\Comment;
use App\Models\Post;
use App\Services\Comments\ManagementService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    use AuthorizesRequests;

    /**
     * Inject the required services into the controller.
     */
    public function __construct(
        protected readonly ManagementService $management,
    ) {}

    /**
     * Store a newly created comment on the given post.
     *
     * Authorisation is handled upstream by StoreCommentRequest.
     */
    public function store(StoreCommentRequest $request, Post $post): JsonResponse|RedirectResponse
    {
        $comment = $this->management->store($request, $post);

        if ($request->wantsJson()) {
            return response()->json($comment, 201);
        }

        return back();
    }

    /**
     * Remove the specified comment from storage.
     *
     * Authorises via the 'delete' policy before proceeding.
     */
    public function destroy(Post $post, Comment $comment, Request $request): JsonResponse|RedirectResponse
    {
        $this->authorize('delete', $comment);

        $this->management->destroy($comment, $request->user());

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return back();
    }
}
