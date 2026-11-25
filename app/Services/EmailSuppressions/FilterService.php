<?php

declare(strict_types=1);

namespace App\Services\EmailSuppressions;

use App\Models\EmailSuppression;
use App\Services\EscapesLikeValues;
use Illuminate\Database\Eloquent\Builder;

class FilterService
{
    use EscapesLikeValues;

    /**
     * Free-text search over the searchable columns only.
     *
     * @param  Builder<EmailSuppression>  $query
     * @return Builder<EmailSuppression>
     */
    public function applySearch(Builder $query, ?string $search): Builder
    {
        if ($search === null || trim($search) === '') {
            return $query;
        }

        $term = $this->escapeLikeValue(trim($search));

        return $query->where(function (Builder $q) use ($term): void {
            $q->where('email', 'like', "%{$term}%");
        });
    }

    /**
     * Filter by reason. Values outside the allow-list are ignored.
     *
     * @param  Builder<EmailSuppression>  $query
     * @return Builder<EmailSuppression>
     */
    public function applyReason(Builder $query, mixed $value): Builder
    {
        if (! is_string($value) || ! in_array($value, EmailSuppression::REASONS, true)) {
            return $query;
        }

        return $query->where('reason', $value);
    }

    /**
     * Apply all filters to the query.
     *
     * @param  Builder<EmailSuppression>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<EmailSuppression>
     */
    public function applyAll(Builder $query, array $filters): Builder
    {
        $query = $this->applySearch($query, $filters['search'] ?? null);

        return $this->applyReason($query, $filters['reason'] ?? null);
    }
}