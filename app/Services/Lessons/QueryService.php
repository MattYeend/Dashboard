<?php

declare(strict_types=1);

namespace App\Services\Lessons;

use App\Models\Course;
use App\Models\Lesson;
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
     * Get paginated lessons with filters.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function getPaginated(
        User $actor,
        array $filters = []
    ): array {
        $query = $this->buildQuery($actor, $filters);
        $paginated = $this->paginate(
            $query,
            max(1, min((int) ($filters['per_page'] ?? 15), 100))
        );

        return array_merge(
            $paginated,
            $this->getPermissions($actor),
            $this->baseData($actor),
        );
    }

    /**
     * Get a single lesson by ID.
     *
     * @return array<string, mixed>
     */
    public function getById(User $user, Lesson $lesson): array
    {
        $lesson->loadMissing([
            'course:id,title,published_at',
            'creator',
            'updater',
            'deleter',
            'restorer',
        ]);

        return array_merge(
            ['lesson' => $this->formatterService->format($lesson)],
            $this->getPermissions($user),
            $this->baseData($user),
        );
    }

    /**
     * Get the courses the actor may attach lessons to or filter by.
     *
     * @return array<int, array{id: int, title: string}>
     */
    public function courseOptions(User $actor): array
    {
        return Course::query()
            ->where('organisation_id', $actor->organisation_id)
            ->when(
                ! $this->isEditor($actor),
                fn (Builder $query) => $query
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now())
            )
            ->orderBy('title')
            ->get(['id', 'title'])
            ->map(fn (Course $course): array => [
                'id' => $course->id,
                'title' => $course->title,
            ])
            ->all();
    }

    /**
     * Build the base query with filters.
     *
     * @param  array<string, mixed>  $filters
     * @return Builder<Lesson>
     */
    protected function buildQuery(User $actor, array $filters): Builder
    {
        $query = Lesson::query()
            ->where('organisation_id', $actor->organisation_id)
            ->with([
                'course:id,title,published_at',
                'creator',
                'updater',
                'deleter',
                'restorer',
            ]);

        if (! $this->isEditor($actor)) {
            $query = $this->filterService->applyPublishedOnly($query);
        }

        $query = $this->filterService->applyAll($query, $filters);

        return $this->applySorting($actor, $query, $filters);
    }

    /**
     * Paginate the query and return as plain array.
     *
     * @param  Builder<Lesson>  $query
     * @return array<string, mixed>
     */
    protected function paginate(Builder $query, int $perPage): array
    {
        $paginator = $query->paginate($perPage)->withQueryString();

        return [
            'lessons' => [
                'data' => array_map(
                    fn (Lesson $lesson) => $this->formatterService->format($lesson, false),
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
     *
     * @return array<string, array<string, bool>>
     */
    protected function getPermissions(User $user): array
    {
        return [
            'permissions_meta' => [
                'can_create' => $user->can('create', Lesson::class),
                'can_view_any' => $user->can('viewAny', Lesson::class),
                'can_export' => $user->can('export', Lesson::class),
            ],
        ];
    }

    /**
     * Get base data for the view.
     *
     * @return array<string, mixed>
     */
    protected function baseData(User $actor): array
    {
        return [
            'sort_fields' => $this->sortingService->getAvailableSortFields(),
            'trash_filters' => $this->trashFilterService->getFilterOptions(),
            'course_options' => $this->courseOptions($actor),
        ];
    }

    /**
     * Apply trash filtering (editors only) and sorting to the query.
     *
     * @param  Builder<Lesson>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<Lesson>
     */
    private function applySorting(User $actor, Builder $query, array $filters): Builder
    {
        if ($this->isEditor($actor)) {
            $query = $this->trashFilterService->applyFilter(
                $query,
                $filters['trashed'] ?? null
            );
        }

        return $this->sortingService->applySorting(
            $query,
            $filters['sort_by'] ?? 'position',
            $filters['sort_direction'] ?? 'asc'
        );
    }

    /**
     * Editors can see unpublished and trashed lessons.
     */
    private function isEditor(User $actor): bool
    {
        return $actor->can('update lessons');
    }
}
