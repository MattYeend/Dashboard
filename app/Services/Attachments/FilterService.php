<?php

namespace App\Services\Attachments;

use App\Models\Attachment;
use App\Services\EscapesLikeValues;
use Illuminate\Database\Eloquent\Builder;

class FilterService
{
    use EscapesLikeValues;

    /**
     * Apply search filter to query.
     *
     * @param  Builder<Attachment>  $query
     * @return Builder<Attachment>
     */
    public function applySearch(Builder $query, ?string $search): Builder
    {
        if ($search === null) {
            return $query;
        }

        $search = $this->escapeLikeValue($search);

        return $query->where('original_filename', 'like', "%{$search}%");
    }

    /**
     * Apply all filters to query.
     *
     * @param  Builder<Attachment>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<Attachment>
     */
    public function applyAll(Builder $query, array $filters): Builder
    {
        return $this->applySearch($query, $filters['search'] ?? null);
    }
}
