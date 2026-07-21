<?php

namespace App\Services\RegistrationInterests;

use App\Models\RegistrationInterest;
use Illuminate\Database\Eloquent\Builder;

class TrashFilterService
{
    /**
     * Apply the trashed filter to the query.
     *
     * @param  Builder<RegistrationInterest>  $query
     * @return Builder<RegistrationInterest>
     */
    public function apply(Builder $query, ?string $trashed): Builder
    {
        return match ($trashed) {
            'only' => $query->onlyTrashed(),
            'with' => $query->withTrashed(),
            default => $query,
        };
    }
}
