<?php

namespace App\Services\RegistrationInterests;

use App\Models\RegistrationInterest;
use Illuminate\Database\Eloquent\Builder;

class SortingService
{
    /**
     * The columns permitted for sorting.
     *
     * @var array<int, string>
     */
    protected array $sortable = ['name', 'email', 'company', 'created_at'];

    /**
     * Apply sorting to the query.
     *
     * @param  Builder<RegistrationInterest>  $query
     * @return Builder<RegistrationInterest>
     */
    public function apply(Builder $query, ?string $sortBy, ?string $sortDirection): Builder
    {
        $column = in_array($sortBy, $this->sortable, true) ? $sortBy : 'created_at';
        $direction = $sortDirection === 'asc' ? 'asc' : 'desc';

        return $query->orderBy($column, $direction);
    }
}
