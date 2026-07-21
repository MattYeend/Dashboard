<?php

namespace App\Services\Posts;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
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
        protected readonly FormatterService $formatterService
    ) {}

    /**
     * Get paginated posts with filters.
     */
    public function getPaginated(
        User $actor,
        array $filters = []
    ): array {
        $query = $this->buildQuery($filters, $actor);
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
     * Get a single post by ID.
     */
    public function getById(
        User $user,
        int $id,
        bool $withTrashed = false
    ): array {
        $post = $this->findPost($id, $withTrashed, $user);

        return array_merge(
            ['post' => $this->formatterService->format($post, $user)],
            $this->getPermissions($user),
            $this->baseData(),
        );
    }

    /**
     * Get data needed to populate create and edit forms.
     */
    public function getFormData(): array
    {
        return [
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'tags' => Tag::orderBy('name')->get(['id', 'name']),
        ];
    }

    /**
     * Build the base query with filters.
     */
    protected function buildQuery(array $filters, User $actor): Builder
    {
        $query = Post::query()
            ->with(['creator', 'updater', 'deleter', 'restorer', 'categories', 'tags'])
            ->withCount(['likes', 'comments'])
            ->with(['likes' => fn ($query) => $query->where('user_id', $actor->id)]);
        $query = $this->filterService->applyAll($query, $filters);

        return $this->applySorting($query, $filters);
    }

    /**
     * Paginate the query and return as plain array.
     */
    protected function paginate(Builder $query, int $perPage, User $actor): array
    {
        $paginator = $query->paginate($perPage)->withQueryString();

        return [
            'posts' => [
                'data' => array_map(
                    fn (Post $post) => $this->formatterService->format($post, $actor),
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
                'can_create' => $user->can('create', Post::class),
                'can_view_any' => $user->can('viewAny', Post::class),
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
        ];
    }

    /**
     * Find a post by ID with optional trashed records.
     */
    private function findPost(
        int $id,
        bool $withTrashed = false,
        ?User $user = null
    ): Post {
        $query = Post::query()
            ->with(['creator', 'updater', 'deleter', 'restorer', 'categories', 'tags'])
            ->withCount(['likes', 'comments'])
            ->with(['comments' => function ($query) use ($user) {
                $query->with('creator')->withCount('likes');

                if ($user) {
                    $query->with(['likes' => fn ($query) => $query->where('user_id', $user->id)]);
                }
            }]);

        if ($user) {
            $query->with(['likes' => fn ($query) => $query->where('user_id', $user->id)]);
        }

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->findOrFail($id);
    }

    /**
     * Apply sorting to the query.
     */
    private function applySorting(Builder $query, array $filters): Builder
    {
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
