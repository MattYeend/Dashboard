<?php

declare(strict_types=1);

namespace App\Services\EmailTemplates;

use App\Models\EmailTemplate;
use App\Services\EscapesLikeValues;
use Illuminate\Database\Eloquent\Builder;

class FilterService
{
    use EscapesLikeValues;

    /**
     * Free-text search over the searchable columns only.
     *
     * @param  Builder<EmailTemplate>  $query
     * @return Builder<EmailTemplate>
     */
    public function applySearch(Builder $query, ?string $search): Builder
    {
        if ($search === null || trim($search) === '') {
            return $query;
        }

        $term = $this->escapeLikeValue(trim($search));

        return $query->where(function (Builder $q) use ($term): void {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('subject', 'like', "%{$term}%");
        });
    }

    /**
     * Apply all filters to the query.
     *
     * @param  Builder<EmailTemplate>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<EmailTemplate>
     */
    public function applyAll(Builder $query, array $filters): Builder
    {
        return $this->applySearch($query, $filters['search'] ?? null);
    }
}
