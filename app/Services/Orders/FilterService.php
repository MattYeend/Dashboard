<?php

namespace App\Services\Orders;

use App\Models\Order;
use App\Services\EscapesLikeValues;
use Illuminate\Database\Eloquent\Builder;

class FilterService
{
    use EscapesLikeValues;

    /**
     * Apply search filter to query.
     *
     * @param  Builder<Order>  $query
     * @return Builder<Order>
     */
    public function applySearch(Builder $query, ?string $search): Builder
    {
        if ($search === null) {
            return $query;
        }

        $search = $this->escapeLikeValue($search);

        return $query->where(function (Builder $q) use ($search): void {
            $q->where('order_number', 'like', "%{$search}%")
                ->orWhere('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhere('notes', 'like', "%{$search}%");
        });
    }

    /**
     * Apply status filter to query.
     *
     * @param  Builder<Order>  $query
     * @return Builder<Order>
     */
    public function applyStatus(Builder $query, ?int $statusId): Builder
    {
        if ($statusId === null) {
            return $query;
        }

        return $query->where('status_id', $statusId);
    }

    /**
     * Apply all filters to query.
     *
     * @param  Builder<Order>  $query
     * @param  array<string,mixed>  $filters
     * @return Builder<Order>
     */
    public function applyAll(Builder $query, array $filters): Builder
    {
        $query = $this->applySearch($query, $filters['search'] ?? null);

        return $this->applyStatus(
            $query,
            $filters['status_id'] ?? null
        );
    }
}
