<?php

namespace App\Services\Pipelines;

use App\Models\Pipeline;
use App\Services\EscapesLikeValues;
use Illuminate\Database\Eloquent\Builder;

class FilterService
{
    use EscapesLikeValues;

    /**
     * Apply a search filter to the query.
     *
     * @param  Builder<Pipeline>  $query
     * @return Builder<Pipeline>
     */
    public function applySearch(Builder $query, ?string $search): Builder
    {
        if ($search === null) {
            return $query;
        }

        $search = $this->escapeLikeValue($search);

        return $query->where(function (Builder $q) use ($search): void {
            $q->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        });
    }

    /**
     * Apply a status filter to the query.
     *
     * @param  Builder<Pipeline>  $query
     * @return Builder<Pipeline>
     */
    public function applyStatus(Builder $query, ?int $statusId): Builder
    {
        if ($statusId === null) {
            return $query;
        }

        return $query->where('status_id', $statusId);
    }

    /**
     * Apply a default-pipeline filter to the query.
     *
     * @param  Builder<Pipeline>  $query
     * @return Builder<Pipeline>
     */
    public function applyIsDefault(Builder $query, ?bool $isDefault): Builder
    {
        if ($isDefault === null) {
            return $query;
        }

        return $query->where('is_default', $isDefault);
    }

    /**
     * Apply an assignee filter to the query.
     *
     * @param  Builder<Pipeline>  $query
     * @return Builder<Pipeline>
     */
    public function applyAssignedTo(Builder $query, ?int $assignedTo): Builder
    {
        if ($assignedTo === null) {
            return $query;
        }

        return $query->where('assigned_to', $assignedTo);
    }

    /**
     * Apply all filters to the query.
     *
     * @param  Builder<Pipeline>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<Pipeline>
     */
    public function applyAll(Builder $query, array $filters): Builder
    {
        $query = $this->applySearch($query, $filters['search'] ?? null);
        $query = $this->applyStatus($query, $filters['status_id'] ?? null);
        $query = $this->applyIsDefault($query, $filters['is_default'] ?? null);

        return $this->applyAssignedTo($query, $filters['assigned_to'] ?? null);
    }
}
