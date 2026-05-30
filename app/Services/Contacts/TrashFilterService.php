<?php

namespace App\Services\Contacts;

use Illuminate\Database\Eloquent\Builder;

class TrashFilterService
{
    /**
     * Apply trash filter to the query.
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
     */
    public function includesTrashed(?string $trashed): bool
    {
        return in_array($trashed, ['with', 'only'], true);
    }

    /**
     * Check if query is only trashed records.
     */
    public function isOnlyTrashed(?string $trashed): bool
    {
        return $trashed === 'only';
    }
}
