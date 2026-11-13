<?php

declare(strict_types=1);

namespace App\Services\PaymentStatuses;

use App\Models\PaymentStatus;
use App\Services\EscapesLikeValues;
use Illuminate\Database\Eloquent\Builder;

class FilterService
{
    use EscapesLikeValues;

    /**
     * Apply search filter to query.
     *
     * @param  Builder<PaymentStatus>  $query
     * @return Builder<PaymentStatus>
     */
    public function applySearch(Builder $query, ?string $search): Builder
    {
        if ($search === null || $search === '') {
            return $query;
        }

        $search = $this->escapeLikeValue($search);

        return $query->where(function (Builder $q) use ($search): void {
            $q->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        });
    }

    /**
     * Apply all filters to query.
     *
     * @param  Builder<PaymentStatus>  $query
     * @param  array<string,mixed>  $filters
     * @return Builder<PaymentStatus>
     */
    public function applyAll(Builder $query, array $filters): Builder
    {
        return $this->applySearch($query, $filters['search'] ?? null);
    }
}
