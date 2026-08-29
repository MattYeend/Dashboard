<?php

namespace App\Services\NotificationBroadcasts;

use App\Models\NotificationBroadcast;
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
     * Get paginated notification broadcasts with filters.
     */
    public function getPaginated(
        User $user,
        array $filters = []
    ): array {
        $query = $this->buildQuery($filters);
        $paginated = $this->paginate(
            $query,
            min((int) ($filters['per_page'] ?? 15), 100)
        );

        return array_merge(
            $paginated,
            $this->getPermissions($user),
            $this->baseData(),
        );
    }

    /**
     * Get a single notification broadcast by ID.
     */
    public function getById(
        User $user,
        int $id,
        bool $withTrashed = false
    ): array {
        $notificationBroadcast = $this->findNotificationBroadcast($id, $withTrashed);

        return array_merge(
            ['notificationBroadcast' => $this->formatterService->format($notificationBroadcast)],
            $this->getPermissions($user),
            $this->baseData(),
        );
    }

    /**
     * Get data needed to populate create and edit forms.
     *
     * No dropdown data is needed for notification broadcasts, but this is
     * kept for parity with the other modules' Controller@create/@edit calls.
     */
    public function getFormData(): array
    {
        return [];
    }

    /**
     * Build the base query with filters.
     */
    protected function buildQuery(array $filters): Builder
    {
        $query = NotificationBroadcast::query()->with([
            'creator',
            'updater',
            'deleter',
            'restorer',
            'sender',
        ]);
        $query = $this->filterService->applyAll($query, $filters);

        return $this->applySorting($query, $filters);
    }

    /**
     * Paginate the query and return as a plain array.
     */
    protected function paginate(Builder $query, int $perPage): array
    {
        $paginator = $query->paginate($perPage)->withQueryString();

        return [
            'notificationBroadcasts' => [
                'data' => array_map(
                    fn (NotificationBroadcast $notificationBroadcast) => $this->formatterService->format($notificationBroadcast),
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
                'can_create' => $user->can('create', NotificationBroadcast::class),
                'can_view_any' => $user->can('viewAny', NotificationBroadcast::class),
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
     * Find a notification broadcast by ID with optional trashed records.
     */
    private function findNotificationBroadcast(int $id, bool $withTrashed = false): NotificationBroadcast
    {
        $query = NotificationBroadcast::query()->with([
            'creator',
            'updater',
            'deleter',
            'restorer',
            'sender',
        ]);

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->findOrFail($id);
    }

    /**
     * Apply sorting and trash filtering to the query.
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
