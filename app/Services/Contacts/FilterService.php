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
     * Apply country filter to query.
     *
     * @param  Builder<Contact>  $query
     * @return Builder<Contact>
     */
    public function applyCountry(Builder $query, ?string $country): Builder
    {
        if ($country === null) {
            return $query;
        }

        return $query->where('country', $country);
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
        return $this->applySearch($query, $filters['search'] ?? null);
    }
}
