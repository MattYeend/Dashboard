<?php

namespace App\Services\Addresses;

use App\Models\Address;
use App\Services\EscapesLikeValues;
use Illuminate\Database\Eloquent\Builder;

class FilterService
{
    use EscapesLikeValues;

    /**
     * Apply search filter to query.
     *
     * @param  Builder<Address>  $query
     * @return Builder<Address>
     */
    public function applySearch(
        Builder $query,
        ?string $search
    ): Builder {
        if ($search === null) {
            return $query;
        }

        $search = $this->escapeLikeValue($search);

        return $query->where(function (Builder $q) use ($search): void {
            $q->where('address_line_one', 'like', "%{$search}%")
                ->orWhere('city', 'like', "%{$search}%")
                ->orWhere('postal_code', 'like', "%{$search}%")
                ->orWhere('country', 'like', "%{$search}%");
        });
    }

    /**
     * Apply country filter to query.
     *
     * @param  Builder<Address>  $query
     * @return Builder<Address>
     */
    public function applyCountry(
        Builder $query,
        ?string $country
    ): Builder {
        if ($country === null) {
            return $query;
        }

        return $query->where('country', $country);
    }

    /**
     * Apply all filters to query.
     *
     * @param  Builder<Address>  $query
     * @param  array<string,mixed>  $filters
     * @return Builder<Address>
     */
    public function applyAll(
        Builder $query,
        array $filters
    ): Builder {
        $query = $this->applySearch($query, $filters['search'] ?? null);

        return $this->applyCountry(
            $query,
            $filters['country']
            ?? null
        );
    }
}
