<?php

namespace App\Services\Permissions;

use App\Models\Permission;
use Illuminate\Database\Eloquent\Builder;

class SortingService
{
    /**
     * Apply sorting to the query.
     *
     * @param  Builder<Permission>  $query
     * @return Builder<Permission>
     */
    public function applySorting(
        Builder $query,
        ?string $sortBy = 'name',
        ?string $sortDirection = 'asc'
    ): Builder {
        $sortBy = $sortBy ?? 'name';
        $sortDirection = strtolower($sortDirection ?? 'asc') === 'asc' ? 'asc' : 'desc';

        return match ($sortBy) {
            'guard_name' => $query->orderBy('guard_name', $sortDirection),
            'updated_at' => $query->orderBy('updated_at', $sortDirection),
            'created_at' => $query->orderBy('created_at', $sortDirection),
            default => $query->orderBy('name', $sortDirection),
        };
    }

    /**
     * Get available sort fields.
     *
     * @return array<string, string>
     */
    public function getAvailableSortFields(): array
    {
        return [
            'name' => 'Name',
            'guard_name' => 'Guard',
            'created_at' => 'Created Date',
        ];
    }
}
