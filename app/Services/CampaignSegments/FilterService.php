<?php

declare(strict_types=1);

namespace App\Services\CampaignSegments;

use App\Models\CampaignSegment;
use App\Services\EscapesLikeValues;
use Illuminate\Database\Eloquent\Builder;

class FilterService
{
    use EscapesLikeValues;

    /**
     * Query string keys this module understands.
     *
     * @var array<int, string>
     */
    private const ALLOWED_KEYS = [
        'search',
        'sort_by',
        'sort_direction',
        'trashed',
        'per_page',
    ];

    /**
     * Longest search term that is applied.
     */
    private const MAX_SEARCH_LENGTH = 255;

    /**
     * Keep only known keys with scalar values.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, string|int>
     */
    public function normalise(array $filters): array
    {
        return array_filter(
            array_intersect_key($filters, array_flip(self::ALLOWED_KEYS)),
            static fn (mixed $value): bool => is_string($value) || is_int($value)
        );
    }

    /**
     * Apply a free-text search over the searchable columns only.
     *
     * @param  Builder<CampaignSegment>  $query
     * @return Builder<CampaignSegment>
     */
    public function applySearch(Builder $query, ?string $search): Builder
    {
        if ($search === null || trim($search) === '') {
            return $query;
        }

        $term = $this->escapeLikeValue(mb_substr(trim($search), 0, self::MAX_SEARCH_LENGTH));

        return $query->where(function (Builder $q) use ($term): void {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%");
        });
    }

    /**
     * Apply all filters to the query.
     *
     * @param  Builder<CampaignSegment>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<CampaignSegment>
     */
    public function applyAll(Builder $query, array $filters): Builder
    {
        $search = $filters['search'] ?? null;

        return $this->applySearch($query, is_string($search) ? $search : null);
    }
}