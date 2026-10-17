<?php

declare(strict_types=1);

namespace App\Services\Courses;

use App\Enums\CourseDifficulty;
use App\Models\Course;
use App\Models\Organisation;
use App\Models\User;
use App\Services\TrashFilterService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Str;

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
     * Get paginated courses with filters.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function getPaginated(User $user, array $filters = []): array
    {
        $query = $this->buildQuery($filters);
        $paginated = $this->paginate(
            $query,
            max(1, min((int) ($filters['per_page'] ?? 15), 100))
        );

        return array_merge(
            $paginated,
            $this->getPermissions($user),
            $this->baseData(),
        );
    }

    /**
     * Get a single course by ID.
     *
     * @return array<string, mixed>
     */
    public function getById(User $user, Course $course): array
    {
        $course->loadMissing(['instructor', 'creator', 'updater', 'deleter', 'restorer']);

        return array_merge(
            ['course' => $this->formatterService->format($course)],
            $this->getPermissions($user),
            $this->baseData(),
        );
    }

    /**
     * Get data needed to populate create and edit forms.
     *
     * Instructors are limited to members of the current organisation.
     *
     * @return array<string, mixed>
     */
    public function getFormData(): array
    {
        return [
            'instructors' => $this->getInstructors(),
            'difficulties' => $this->getDifficultyOptions(),
        ];
    }

    /**
     * Get the users who can be assigned as an instructor.
     *
     * Mirrors the organisation_user check in the Store and Update requests.
     *
     * @return EloquentCollection<int, User>
     */
    protected function getInstructors(): EloquentCollection
    {
        $organisationId = Organisation::current()?->id;

        if ($organisationId === null) {
            return new EloquentCollection();
        }

        return User::query()
            ->whereIn('id', static function (QueryBuilder $query) use ($organisationId): void {
                $query->select('user_id')
                    ->from('organisation_user')
                    ->where('organisation_id', $organisationId);
            })
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    /**
     * Get the difficulty options for the course form select.
     *
     * @return array<int, array{value: string, label: string}>
     */
    protected function getDifficultyOptions(): array
    {
        return array_map(
            static fn (CourseDifficulty $difficulty): array => [
                'value' => $difficulty->value,
                'label' => Str::headline($difficulty->name),
            ],
            CourseDifficulty::cases(),
        );
    }

    /**
     * Build the base query with filters.
     *
     * @param  array<string, mixed>  $filters
     */
    protected function buildQuery(array $filters): Builder
    {
        $query = Course::query()->with([
            'instructor',
            'creator',
            'updater',
            'deleter',
            'restorer',
        ]);

        $query = $this->filterService->applyAll($query, $filters);

        return $this->applySorting($query, $filters);
    }

    /**
     * Paginate the query and return as a plain array.
     *
     * @return array<string, mixed>
     */
    protected function paginate(Builder $query, int $perPage): array
    {
        $paginator = $query->paginate($perPage)->withQueryString();

        return [
            'courses' => [
                'data' => array_map(
                    fn (Course $course) => $this->formatterService->format($course),
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
     * @return array<string, mixed>
     */
    protected function getPermissions(User $user): array
    {
        return [
            'permissions_meta' => [
                'can_create' => $user->can('create', Course::class),
                'can_view_any' => $user->can('viewAny', Course::class),
                'can_export' => $user->can('export', Course::class),
            ],
        ];
    }

    /**
     * Get base data for the view.
     *
     * @return array<string, mixed>
     */
    protected function baseData(): array
    {
        return [
            'sort_fields' => $this->sortingService->getAvailableSortFields(),
            'trash_filters' => $this->trashFilterService->getFilterOptions(),
        ];
    }

    /**
     * Apply sorting and trash filtering to the query.
     *
     * @param  array<string, mixed>  $filters
     */
    private function applySorting(Builder $query, array $filters): Builder
    {
        $query = $this->trashFilterService->applyFilter(
            $query,
            $filters['trashed'] ?? null
        );

        return $this->sortingService->applySorting(
            $query,
            $filters['sort_by'] ?? 'title',
            $filters['sort_direction'] ?? 'asc'
        );
    }
}
