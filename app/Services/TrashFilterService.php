<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

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
            'only' => $query->withoutGlobalScope(SoftDeletingScope::class)
                ->whereNotNull($query->getModel()->getTable().'.deleted_at'),
            'with' => $query->withoutGlobalScope(SoftDeletingScope::class),
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
