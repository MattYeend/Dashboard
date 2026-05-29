<?php

namespace App\Services\Users;

use Illuminate\Database\Eloquent\Builder;

class TrashFilterService
{
    /**
     * Apply trash filter to the query.
     *
     * @param  Builder $query
     * @param  string|null $trashed
     *
     * @return Builder
     */
    public function applyFilter(
        Builder $query,
        ?string $trashed = null
    ): Builder {
        return match ($trashed) {
            'only' => $query->onlyTrashed(),
            'with' => $query->withTrashed(),
            default => $query,
        };
    }

    /**
     * Get available trash filter options.
     *
     * @return array
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
     * Check if query includes trashed records.
     *
     * @param  string|null $trashed
     *
     * @return bool
     */
    public function includesTrashed(?string $trashed): bool
    {
        return in_array($trashed, ['with', 'only'], true);
    }

    /**
     * Check if query is only trashed records.
     *
     * @param  string|null $trashed
     *
     * @return bool
     */
    public function isOnlyTrashed(?string $trashed): bool
    {
        return $trashed === 'only';
    }
}
