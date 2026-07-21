<?php

namespace App\Services\RegistrationInterests;

use App\Models\RegistrationInterest;
use App\Services\EscapesLikeValues;
use Illuminate\Database\Eloquent\Builder;

class FilterService
{
    use EscapesLikeValues;

    /**
     * Apply a search filter across name, email and company.
     *
     * @param  Builder<RegistrationInterest>  $query
     * @return Builder<RegistrationInterest>
     */
    public function apply(Builder $query, ?string $search): Builder
    {
        if (blank($search)) {
            return $query;
        }

        $escaped = $this->escapeLikeValue($search);

        return $query->where(function (Builder $query) use ($escaped) {
            $query->where('name', 'like', "%{$escaped}%")
                ->orWhere('email', 'like', "%{$escaped}%")
                ->orWhere('company', 'like', "%{$escaped}%");
        });
    }
}
