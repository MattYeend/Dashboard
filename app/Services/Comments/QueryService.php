<?php

namespace App\Services\Comments;

use App\Models\Comment;
use App\Models\User;
use App\Services\TrashFilterService;
use Illuminate\Database\Eloquent\Builder;

class QueryService
{
    /**
     * Inject the required services into the query service.
     */
    public function __construct(
        protected readonly SortingService $sortingService,
        protected readonly TrashFilterService $trashFilterService,
        protected readonly FilterService $filterService,
        protected readonly FormatterService $formatterService,
        protected readonly CommentableTypeRegistryService $registry,
    ) {}

    /**
     * Get paginated comments with filters.
     */
    public function getPaginated(
        User $actor,
        array $filters = []
    ): array {
        $query = $this->buildQuery($filters);
        $paginated = $this->paginate(
            $query,
            min((int) ($filters['per_page'] ?? 15), 100),
            $actor
        );

        return array_merge(
            $paginated,
            $this->getPermissions($actor),
            $this->baseData(),
        );
    }

    /**
     * Get a single comment by ID.
     */
    public function getById(
        User $user,
        int $id,
        bool $withTrashed = false
    ): array {
        $comment = $this->findComment($id, $withTrashed);

        return array_merge(
            ['comment' => $this->formatterService->format($comment, $user)],
            $this->getFormData(),
            $this->getPermissions($user),
            $this->baseData(),
        );
    }

    /**
     * Get the data needed to render the "Create Comment" form.
     */
    public function getFormData(): array
    {
        return [
            'commentableTypes' => $this->registry->types(),
        ];
    }

    /**
     * Get the "owner" options for a given commentable type, for the dependent dropdown on the Create/Edit comment form.
     */
    public function getCommentableOptions(string $type): array
    {
        return $this->registry->optionsFor($type);
    }

    /**
     * Get all comments for a specific commentable model, formatted
     * and ready to attach to that model's Show page props.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getForCommentable(
        User $actor,
        string $commentableType,
        int $commentableId
    ): array {
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

    /**
     * Build the base query with filters.
     */
    protected function buildQuery(array $filters): Builder
    {
        $query = Comment::query()->with([
            'commentable',
            'creator',
            'updater',
            'deleter',
            'restorer',
        ]);

        $query = $this->filterService->applyAll($query, $filters);

        return $this->applySorting($query, $filters);
    }

    /**
     * Paginate the query and return as plain array.
     */
    protected function paginate(
        Builder $query,
        int $perPage,
        User $actor
    ): array {
        $paginator = $query->paginate($perPage)->withQueryString();

        return [
            'comments' => [
                'data' => array_map(
                    fn (Comment $comment) => $this->formatterService->format($comment, $actor),
                    $paginator->items()
                ),
                'links' => $paginator->linkCollection()->toArray(),
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'from' => $paginator->firstItem(),
                    'to' => $paginator->lastItem(),
                ],
            ],
        ];
    }

    /**
     * Get user permissions for the authenticated user.
     */
    protected function getPermissions(User $user): array
    {
        return [
            'permissions_meta' => [
                'can_create' => $user->can('create', Comment::class),
                'can_view_any' => $user->can('viewAny', Comment::class),
                'can_export' => $user->can('export', Comment::class),
            ],
        ];
    }

    /**
     * Get base data for the view.
     */
    protected function baseData(): array
    {
        return [
            'sort_fields' => $this->sortingService->getAvailableSortFields(),
            'trash_filters' => $this->trashFilterService->getFilterOptions(),
            'commentableTypes' => $this->registry->types(),
        ];
    }

    /**
     * Find a comment by ID with optional trashed records.
     */
    private function findComment(
        int $id,
        bool $withTrashed = false
    ): Comment {
        $query = Comment::query()->with([
            'commentable',
            'creator',
            'updater',
            'deleter',
            'restorer',
        ]);

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->findOrFail($id);
    }

    /**
     * Apply trash filtering and sorting to the query.
     */
    private function applySorting(
        Builder $query,
        array $filters
    ): Builder {
        $query = $this->trashFilterService->applyFilter(
            $query,
            $filters['trashed'] ?? null
        );

        return $this->sortingService->applySorting(
            $query,
            $filters['sort_by'] ?? 'created_at',
            $filters['sort_direction'] ?? 'desc'
        );
    }
}
