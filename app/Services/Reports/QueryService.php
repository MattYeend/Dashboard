<?php

namespace App\Services\Reports;

use App\Models\Report;
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
        protected readonly ReportTypeRegistryService $registry,
    ) {}

    /**
     * Get paginated reports with filters.
     */
    public function getPaginated(User $actor, array $filters = []): array
    {
        $query = $this->buildQuery($filters);
        $paginated = $this->paginate($query, min((int) ($filters['per_page'] ?? 15), 100), $actor);

        return array_merge(
            $paginated,
            $this->getPermissions($actor),
            $this->baseData(),
        );
    }

    /**
     * Get a single report by ID.
     */
    public function getById(User $user, int $id, bool $withTrashed = false): array
    {
        $report = $this->findReport($id, $withTrashed);

        return array_merge(
            ['report' => $this->formatterService->format($report, $user)],
            $this->getFormData(),
            $this->getPermissions($user),
            $this->baseData(),
        );
    }

    /**
     * Get the data needed to render the "Create Report" / "Edit Report" forms.
     */
    public function getFormData(): array
    {
        return [
            'reportTypes' => $this->registry->types(),
        ];
    }

    /**
     * Build the base query with filters.
     *
     * @param  array<string, mixed>  $filters
     * @return Builder<Report>
     */
    protected function buildQuery(array $filters): Builder
    {
        $query = Report::query()->with(['creator', 'updater', 'deleter', 'restorer']);

        $query = $this->filterService->applyAll($query, $filters);

        return $this->applySorting($query, $filters);
    }

    /**
     * Paginate the query and return as plain array.
     *
     * @param  Builder<Report>  $query
     */
    protected function paginate(Builder $query, int $perPage, User $actor): array
    {
        $paginator = $query->paginate($perPage)->withQueryString();

        return [
            'reports' => [
                'data' => array_map(
                    fn (Report $report) => $this->formatterService->format($report, $actor),
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
                'can_create' => $user->can('create', Report::class),
                'can_view_any' => $user->can('viewAny', Report::class),
                'can_export' => $user->can('export', Report::class),
                'can_schedule' => $user->can('schedule', Report::class),
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
            'reportTypes' => $this->registry->types(),
        ];
    }

    /**
     * Find a report by ID with optional trashed records.
     */
    private function findReport(int $id, bool $withTrashed = false): Report
    {
        $query = Report::query()->with(['creator', 'updater', 'deleter', 'restorer']);

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->findOrFail($id);
    }

    /**
     * Apply trash filtering and sorting to the query.
     *
     * @param  Builder<Report>  $query
     * @return Builder<Report>
     */
    private function applySorting(Builder $query, array $filters): Builder
    {
        $query = $this->trashFilterService->applyFilter($query, $filters['trashed'] ?? null);

        return $this->sortingService->applySorting(
            $query,
            $filters['sort_by'] ?? 'created_at',
            $filters['sort_direction'] ?? 'desc'
        );
    }
}
