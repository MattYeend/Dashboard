<?php

namespace App\Services\Reports;

use App\Models\Report;
use Illuminate\Database\Eloquent\Builder;

class SortingService
{
    /**
     * @param  Builder<Report>  $query
     * @return Builder<Report>
     */
    public function applySorting(
        Builder $query,
        ?string $sortBy = 'created_at',
        ?string $sortDirection = 'desc'
    ): Builder {
        $sortBy = in_array($sortBy, ['title', 'type', 'next_run_at', 'created_at', 'updated_at'], true)
            ? $sortBy
            : 'created_at';
        $sortDirection = strtolower($sortDirection ?? 'desc') === 'asc' ? 'asc' : 'desc';

        return $query->orderBy($sortBy, $sortDirection);
    }

    /**
     * @return array<string, string>
     */
    public function getAvailableSortFields(): array
    {
        return [
            'title' => 'Title',
            'type' => 'Type',
            'next_run_at' => 'Next Run',
            'created_at' => 'Created Date',
            'updated_at' => 'Updated Date',
        ];
    }
}
