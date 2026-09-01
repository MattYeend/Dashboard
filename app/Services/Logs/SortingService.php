<?php

namespace App\Services\Logs;

use App\Models\Log;
use Illuminate\Database\Eloquent\Builder;

class SortingService
{
    /**
     * Apply sorting to the query.
     *
     * @param  Builder<Log>  $query
     * @return Builder<Log>
     */
    public function applySorting(
        Builder $query,
        ?string $sortBy = 'created_at',
        ?string $sortDirection = 'desc'
    ): Builder {
        $sortBy = $sortBy ?? 'created_at';
        $sortDirection = strtolower($sortDirection ?? 'desc') === 'asc' ? 'asc' : 'desc';

        return match ($sortBy) {
            'action_id' => $query->orderBy('action_id', $sortDirection),
            'logged_in_user_id' => $query->orderBy('logged_in_user_id', $sortDirection),
            'related_to_user_id' => $query->orderBy('related_to_user_id', $sortDirection),
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
            'created_at' => 'Date',
            'action_id' => 'Action',
        ];
    }
}
