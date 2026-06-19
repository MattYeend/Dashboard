<?php

namespace App\Services\Users;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class SortingService
{
    /**
     * Apply sorting to query.
     *
     * @param  Builder<User>  $query
     * @return Builder<User>
     */
    public function applySorting(
        Builder $query,
        ?string $sortBy = 'created_at',
        ?string $sortDirection = 'asc'
    ): Builder {
        $sortBy = $sortBy ?? 'created_at';
        $sortDirection = strtolower(
            $sortDirection ?? 'asc'
        ) === 'asc' ? 'asc' : 'desc';

        return match ($sortBy) {
            'name' => $query->orderBy('name', $sortDirection),
            'email' => $query->orderBy('email', $sortDirection),
            'role' => $query->orderBy('role', $sortDirection),
            'updated_at' => $query->orderBy('updated_at', $sortDirection),
            default => $query->orderBy('created_at', $sortDirection),
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
            'email' => 'Email',
            'role' => 'Role',
            'created_at' => 'Created Date',
            'updated_at' => 'Updated Date',
        ];
    }
}
