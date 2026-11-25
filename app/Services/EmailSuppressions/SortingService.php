<?php

declare(strict_types=1);

namespace App\Services\EmailSuppressions;

use App\Models\EmailSuppression;
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
        'email' => 'Email',
        'reason' => 'Reason',
        'suppressed_at' => 'Suppressed at',
        'created_at' => 'Created',
    ];

    /**
     * Get available sort fields.
     *
     * @return array<string, string>
     */
    public function getAvailableSortFields(): array
    {
        return self::SORT_FIELDS;
    }

    /**
     * Apply sorting to the query.
     *
     * @param  Builder<EmailSuppression>  $query
     * @return Builder<EmailSuppression>
     */
    public function applySorting(
        Builder $query,
        ?string $sortBy = 'created_at',
        ?string $sortDirection = 'desc'
    ): Builder {
        $column = array_key_exists((string) $sortBy, self::SORT_FIELDS)
            ? (string) $sortBy
            : 'created_at';

        $direction = strtolower($sortDirection ?? 'desc') === 'asc' ? 'asc' : 'desc';

        return $query->orderBy($column, $direction)->orderBy('id', 'desc');
    }
}
