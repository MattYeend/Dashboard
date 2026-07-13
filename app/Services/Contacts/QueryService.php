<?php

namespace App\Services\Contacts;

use App\Models\Contact;
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
        protected readonly ContactableTypeRegistryService $registry,
    ) {}

    /**
     * Get paginated contacts with filters.
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
     * Get a single contact by ID.
     */
    public function getById(
        User $user,
        int $id,
        bool $withTrashed = false
    ): array {
        $contact = $this->findContact($id, $withTrashed);

        return array_merge(
            ['contact' => $this->formatterService->format($contact)],
            $this->getPermissions($user),
            $this->baseData(),
        );
    }

    /**
     * Get the data needed to render the "Create Contact" form.
     */
    public function getFormData(): array
    {
        return [
            'contactableTypes' => $this->registry->types(),
        ];
    }

    /**
     * Get the "owner" options for a given contactable type, for the dependent dropdown on the Create/Edit contact form.
     */
    public function getContactableOptions(string $type): array
    {
        return $this->registry->optionsFor($type);
    }

    /**
     * Build the base query with filters.
     */
    protected function buildQuery(array $filters): Builder
    {
        $query = Contact::query()
            ->with(['contactable', 'creator', 'updater', 'deleter', 'restorer']);

        $query = $this->filterService->applyAll($query, $filters);

        return $this->applySorting($query, $filters);
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
            'contacts' => [
                'data' => array_map(
                    fn (Contact $contact) => $this->formatterService->format($contact),
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
            'contactableTypes' => $this->registry->types(),
        ];
    }

    /**
     * Find a contact by ID with optional trashed records.
     */
    private function findContact(
        int $id,
        bool $withTrashed = false
    ): Contact {
        $query = Contact::query()
            ->with(['contactable', 'creator', 'updater', 'deleter', 'restorer']);

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
            $filters['sort_by'] ?? 'email',
            $filters['sort_direction'] ?? 'asc'
        );
    }
}
