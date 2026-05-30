<?php

namespace App\Services\Contacts;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class QueryService
{
    /**
     * Inject the required services into the query service.
     */
    public function __construct(
        protected SortingService $sortingService,
        protected TrashFilterService $trashFilterService,
        protected FilterService $filterService,
        protected FormatterService $formatterService
    ) {}

    /**
     * Get paginated contacts with filters.
     */
    public function getPaginated(array $filters = []): array
    {
        $query = $this->buildQuery($filters);
        $paginated = $this->paginate($query, $filters['per_page'] ?? 15);

        return array_merge(
            $paginated,
            $this->getPermissions(),
            $this->baseData(),
        );
    }

    /**
     * Get a single contact by ID.
     */
    public function getById(int $id, bool $withTrashed = false): array
    {
        $contact = $this->findContact($id, $withTrashed);

        return array_merge(
            ['contact' => $this->formatterService->format($contact)],
            $this->getPermissions(),
            $this->baseData(),
        );
    }

    /**
     * Build the base query with filters.
     */
    protected function buildQuery(array $filters): Builder
    {
        $query = Contact::query();
        $query = $this->filterService->applyAll($query, $filters);

        return $this->applySorting($query, $filters);
    }

    /**
     * Paginate the query and return as plain array.
     */
    protected function paginate(Builder $query, int $perPage): array
    {
        $paginator = $query->paginate($perPage);

        return [
            'company_contacts' => $paginator->items(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
        ];
    }

    /**
     * Get user permissions for the authenticated user.
     */
    protected function getPermissions(): array
    {
        /** @var User $user */
        $user = auth()->user();

        if (! $user) {
            return ['permissions_meta' => []];
        }

        return [
            'permissions_meta' => [
                'can_create' => $user->can('create', Contact::class),
                'can_view_any' => $user->can('viewAny', Contact::class),
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
     * Find a contact by ID with optional trashed records.
     */
    private function findContact(
        int $id,
        bool $withTrashed = false
    ): Contact {
        $query = Contact::query();

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
