<?php

namespace App\Services\Contacts;

use App\Models\Contact;
use App\Services\EscapesLikeValues;
use Illuminate\Database\Eloquent\Builder;

class FilterService
{
    use EscapesLikeValues;

    /**
     * Apply search filter to query.
     *
     * @param  Builder<Contact>  $query
     * @return Builder<Contact>
     */
    public function applySearch(Builder $query, ?string $search): Builder
    {
        if ($search === null) {
            return $query;
        }

        $search = $this->escapeLikeValue($search);

        return $query->where(function (Builder $q) use ($search): void {
            $q->where('phone', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
        });
    }

    /**
     * Apply a tag filter to the query.
     *
     * @param  Builder<Contact>  $query
     * @return Builder<Contact>
     */
    public function applyTag(Builder $query, int|string|null $tagId): Builder
    {
        if ($tagId === null || $tagId === '') {
            return $query;
        }

        return $query->whereHas('tags', function (Builder $q) use ($tagId): void {
            $q->where('tags.id', $tagId);
        });
    }

    /**
     * Apply all filters to query.
     *
     * @param  Builder<Contact>  $query
     * @param  array<string,mixed>  $filters
     * @return Builder<Contact>
     */
    public function applyAll(Builder $query, array $filters): Builder
    {
        $query = $this->applySearch($query, $filters['search'] ?? null);

        return $this->applyTag($query, $filters['tag_id'] ?? null);
    }
}
