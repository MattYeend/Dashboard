<?php

namespace App\Services\Labels;

use App\Models\Label;
use Illuminate\Database\Eloquent\Builder;

class SortingService
{
    /**
     * Apply sorting to the query.
     *
     * @param  Builder<Label>  $query
     * @return Builder<Label>
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
