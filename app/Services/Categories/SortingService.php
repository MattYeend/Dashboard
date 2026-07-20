<?php

namespace App\Services\Categories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;

class SortingService
{
    /**
     * Apply sorting to the query.
     *
     * @param  Builder<Category>  $query
     * @return Builder<Category>
     */
    public function applySorting(
        Builder $query,
        ?string $sortBy = 'created_at',
        ?string $sortDirection = 'asc'
    ): Builder {
        $sortBy = $sortBy ?? 'created_at';
        $sortDirection = strtolower($sortDirection ?? 'asc') === 'asc' ? 'asc' : 'desc';

        return match ($sortBy) {
            'name' => $query->orderBy('name', $sortDirection),
            'slug' => $query->orderBy('slug', $sortDirection),
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
            'slug' => 'Slug',
            'created_at' => 'Created Date',
        ];
    }
}
