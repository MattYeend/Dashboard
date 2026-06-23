<?php

namespace App\Services\Users;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class FilterService
{
    /**
     * Apply search filter to query.
     *
     * @param  Builder<User>  $query
     * @return Builder<User>
     */
    public function applySearch(Builder $query, ?string $search): Builder
    {
        if ($search === null) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($search): void {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
        });
    }

    /**
     * Apply role filter to query.
     *
     * @param  Builder<User>  $query
     * @return Builder<User>
     */
    public function applyRole(Builder $query, ?string $role): Builder
    {
        if ($role === null || $role === '') {
            return $query;
        }

        return $query->where('role', $role);
    }

    /**
     * Apply all filters to query.
     *
     * @param  Builder<User>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<User>
     */
    public function applyAll(Builder $query, array $filters): Builder
    {
        $query = $this->applySearch($query, $filters['search'] ?? null);

        return $this->applyRole($query, $filters['role'] ?? null);
    }
}
