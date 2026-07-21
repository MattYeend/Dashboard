<?php

namespace App\Services\RegistrationInterests;

use App\Models\RegistrationInterest;
use App\Models\User;

class QueryService
{
    public function __construct(
        protected SortingService $sorting,
        protected TrashFilterService $trashFilter,
        protected FilterService $filter,
        protected FormatterService $formatter,
    ) {}

    /**
     * Get a paginated, filtered, sorted list of registration interests.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function getPaginated(User $user, array $filters): array
    {
        $query = RegistrationInterest::query();

        $query = $this->trashFilter->apply($query, $filters['trashed'] ?? null);
        $query = $this->filter->apply($query, $filters['search'] ?? null);
        $query = $this->sorting->apply($query, $filters['sort_by'] ?? null, $filters['sort_direction'] ?? null);

        $perPage = (int) ($filters['per_page'] ?? 15);
        $interests = $query->paginate($perPage)->withQueryString();

        return [
            'interests' => $this->formatter->formatMany($interests->getCollection()),
            'meta' => [
                'current_page' => $interests->currentPage(),
                'last_page' => $interests->lastPage(),
                'per_page' => $interests->perPage(),
                'total' => $interests->total(),
                'from' => $interests->firstItem(),
                'to' => $interests->lastItem(),
            ],
            'permissions' => [
                'can_create' => false,
                'can_view_any' => $user->can('viewAny', RegistrationInterest::class),
            ],
        ];
    }

    /**
     * Get a single registration interest by id, formatted for the frontend.
     *
     * @return array<string, mixed>
     */
    public function getById(int $id): array
    {
        $interest = RegistrationInterest::withTrashed()->findOrFail($id);

        return [
            'interest' => $this->formatter->format($interest),
        ];
    }
}
