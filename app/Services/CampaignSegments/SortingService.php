<?php

declare(strict_types=1);

namespace App\Services\CampaignSegments;

use App\Models\CampaignSegment;
use Illuminate\Database\Eloquent\Builder;

class SortingService
{
    /**
     * Allow-list of sortable columns. Anything else falls back to created_at,
     * so a request can never inject a column name into ORDER BY.
     *
     * @var array<string, string>
     */
    private const SORT_FIELDS = [
        'name' => 'Name',
        'estimated_recipients_count' => 'Estimated recipients',
        'last_refreshed_at' => 'Last refreshed',
        'created_at' => 'Created',
    ];

    /**
     * Apply sorting to the query.
     *
     * @param  Builder<CampaignSegment>  $query
     * @return Builder<CampaignSegment>
     */
    public function applySorting(
        Builder $query,
        ?string $sortBy = 'created_at',
        ?string $sortDirection = 'desc'
    ): Builder {
        $column = array_key_exists((string) $sortBy, self::SORT_FIELDS) ? (string) $sortBy : 'created_at';
        $direction = strtolower((string) $sortDirection) === 'asc' ? 'asc' : 'desc';

        return $query->orderBy($column, $direction)->orderBy('id', 'desc');
    }

    /**
     * Get available sort fields.
     *
     * @return array<string, string>
     */
    public function getAvailableSortFields(): array
    {
        return self::SORT_FIELDS;
    }
}
