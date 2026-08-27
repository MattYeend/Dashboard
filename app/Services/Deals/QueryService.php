<?php

namespace App\Services\Deals;

use App\Models\Company;
use App\Models\Deal;
use App\Models\DealStatus;
use App\Models\Invoice;
use App\Models\Pipeline;
use App\Models\PipelineStage;
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
     * Get paginated deals with filters.
     */
    public function getPaginated(
        User $actor,
        array $filters = []
    ): array {
        $query = $this->buildQuery($filters);
        $paginated = $this->paginate(
            $query,
            min((int) ($filters['per_page'] ?? 15), 100)
        );

        return array_merge(
            $paginated,
            $this->getPermissions($actor),
            $this->baseData(),
        );
    }

    /**
     * Get a single deal by ID.
     */
    public function getById(
        User $user,
        int $id,
        bool $withTrashed = false
    ): array {
        $deal = $this->findDeal(
            $id,
            $withTrashed
        );

        return array_merge(
            ['deal' => $this->formatterService->format($deal)],
            $this->getPermissions($user),
            $this->baseData(),
        );
    }

    /**
     * Get dropdown data required by the deal create/edit forms.
     */
    public function getFormData(): array
    {
        return [
            'pipelines' => Pipeline::query()
                ->select('id', 'title')
                ->orderBy('title')
                ->get(),
            'pipeline_stages' => PipelineStage::query()
                ->select('id', 'pipeline_id', 'title')
                ->orderBy('position')
                ->get(),
            'deal_statuses' => DealStatus::query()
                ->select('id', 'title', 'background_colour', 'text_colour')
                ->orderBy('title')
                ->get(),
            'companies' => Company::query()
                ->select('id', 'name')
                ->orderBy('name')
                ->get(),
            'invoices' => Invoice::query()
                ->select('id', 'invoice_number')
                ->orderBy('invoice_number')
                ->get(),
        ];
    }

    /**
     * Build the base query with filters.
     */
    protected function buildQuery(
        array $filters
    ): Builder {
        $query = Deal::query();
        $query = $this->filterService->applyAll(
            $query,
            $filters
        );

        return $this->applySorting(
            $query,
            $filters
        );
    }

    /**
     * Paginate the query and return as plain array.
     */
    protected function paginate(
        Builder $query,
        int $perPage
    ): array {
        $paginator = $query->paginate($perPage)->withQueryString();

        return [
            'deals' => [
                'data' => array_map(
                    fn (Deal $deal) => $this->formatterService->format($deal),
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
                'can_create' => $user->can('create', Deal::class),
                'can_view_any' => $user->can('viewAny', Deal::class),
                'can_export' => $user->can('export', Deal::class),
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
     * Find a deal by ID with optional trashed records.
     */
    private function findDeal(
        int $id,
        bool $withTrashed = false
    ): Deal {
        $query = Deal::query();

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->findOrFail($id);
    }

    /**
     * Apply sorting to the query.
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
            $filters['sort_by'] ?? 'title',
            $filters['sort_direction'] ?? 'asc'
        );
    }
}
