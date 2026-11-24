<?php

declare(strict_types=1);

namespace App\Services\CampaignRecipients;

use App\Models\CampaignRecipient;
use App\Services\EscapesLikeValues;
use Illuminate\Database\Eloquent\Builder;

class FilterService
{
    use EscapesLikeValues;

    /**
     * Free-text search over the searchable columns only.
     *
     * @param  Builder<CampaignRecipient>  $query
     * @return Builder<CampaignRecipient>
     */
    public function applySearch(Builder $query, ?string $search): Builder
    {
        if ($search === null || trim($search) === '') {
            return $query;
        }

        $term = $this->escapeLikeValue(trim($search));

        return $query->where(function (Builder $q) use ($term): void {
            $q->where('email', 'like', "%{$term}%")
                ->orWhere('name', 'like', "%{$term}%");
        });
    }

    /**
     * Filter by status. Values outside the allow-list are ignored.
     *
     * @param  Builder<CampaignRecipient>  $query
     * @return Builder<CampaignRecipient>
     */
    public function applyStatus(Builder $query, mixed $value): Builder
    {
        if (! is_string($value) || ! in_array($value, CampaignRecipient::STATUSES, true)) {
            return $query;
        }

        return $query->where('status', $value);
    }

    /**
     * Apply all filters to the query.
     *
     * @param  Builder<CampaignRecipient>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<CampaignRecipient>
     */
    public function applyAll(Builder $query, array $filters): Builder
    {
        $query = $this->applySearch($query, $filters['search'] ?? null);

        return $this->applyStatus($query, $filters['status'] ?? null);
    }
}
