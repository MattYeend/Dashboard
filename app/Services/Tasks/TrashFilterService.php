<?php

namespace App\Services\Tasks;

use Illuminate\Database\Eloquent\Builder;

class TrashFilterService
{
    /**
     * Apply trash filter to the query.
     */
    public function applyFilter(Builder $query, ?string $trashed = null): Builder
    {
        return match ($trashed) {
            'only' => $query->onlyTrashed(),
            'with' => $query->withTrashed(),
            default => $query,
        };
    }

    /**
     * Get available trash filter options.
     *
     * @return array<string, string>
     */
    public function getFilterOptions(): array
    {
        return [
            '' => 'Active Only',
            'with' => 'Include Deleted',
            'only' => 'Deleted Only',
        ];
    }

    /**
     * Check if the query includes trashed records.
     */
    public function includesTrashed(?string $trashed): bool
    {
        return in_array($trashed, ['with', 'only'], true);
    }

    /**
     * Check if the query is for trashed records only.
     */
    public function isOnlyTrashed(?string $trashed): bool
    {
        return $trashed === 'only';
    }
}
