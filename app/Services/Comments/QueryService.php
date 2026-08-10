<?php

namespace App\Services\Comments;

use App\Models\Comment;
use App\Models\User;

class QueryService
{
    /**
     * Inject the required services into the query service.
     */
    public function __construct(
        protected readonly SortingService $sortingService,
        protected readonly FilterService $filterService,
        protected readonly FormatterService $formatterService,
    ) {}

    /**
     * Get a paginated list of comments.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function getPaginated(User $actor, array $filters): array
    {
        $query = Comment::query()->with(['creator', 'updater', 'commentable']);

        if (($filters['trashed'] ?? null) === 'with') {
            $query->withTrashed();
        } elseif (($filters['trashed'] ?? null) === 'only') {
            $query->onlyTrashed();
        }

        $query = $this->filterService->applyAll($query, $filters);

        $query = $this->sortingService->applySorting(
            $query,
            $filters['sort_by'] ?? 'created_at',
            $filters['sort_direction'] ?? 'desc'
        );

        $perPage = min((int) ($filters['per_page'] ?? 15), 100);

        $comments = $query->paginate($perPage)->withQueryString();

        return ['comments' => $comments];
    }

    /**
     * Get a single comment by ID.
     *
     * @return array<string, mixed>
     */
    public function getById(int $id): array
    {
        $comment = Comment::withTrashed()
            ->with(['creator', 'updater', 'deleter', 'restorer', 'commentable'])
            ->findOrFail($id);

        return ['comment' => $comment];
    }

    /**
     * Get all comments for a specific commentable model, formatted
     * and ready to attach to that model's Show page props.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getForCommentable(User $actor, string $commentableType, int $commentableId): array
    {
        $comments = Comment::query()
            ->where('commentable_type', $commentableType)
            ->where('commentable_id', $commentableId)
            ->with(['creator', 'likes'])
            ->latest()
            ->get();

        return $comments->map(
            fn (Comment $comment) => $this->formatterService->format($comment, $actor)
        )->all();
    }
}
