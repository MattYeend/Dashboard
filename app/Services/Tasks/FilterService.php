<?php

namespace App\Services\Tasks;

use App\Models\Task;
use Illuminate\Database\Eloquent\Builder;

class FilterService
{
    /**
     * Apply a search filter to the query.
     *
     * @param  Builder<Task>  $query
     * @return Builder<Task>
     */
    public function applySearch(Builder $query, ?string $search): Builder
    {
        if ($search === null) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($search): void {
            $q->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        });
    }

    /**
     * Apply status filter to the query.
     *
     * @param  Builder<Task>  $query
     * @return Builder<Task>
     */
    public function applyStatusFilter(Builder $query, ?int $statusId): Builder
    {
        if ($statusId === null) {
            return $query;
        }

        return $query->where('status_id', $statusId);
    }

    /**
     * Apply assignee filter to the query.
     *
     * @param  Builder<Task>  $query
     * @return Builder<Task>
     */
    public function applyAssigneeFilter(Builder $query, ?int $assignedTo): Builder
    {
        if ($assignedTo === null) {
            return $query;
        }

        return $query->where('assigned_to', $assignedTo);
    }

    /**
     * Apply all filters to the query.
     *
     * @param  Builder<Task>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<Task>
     */
    public function applyAll(Builder $query, array $filters): Builder
    {
        $query = $this->applySearch($query, $filters['search'] ?? null);
        $query = $this->applyStatusFilter($query, isset($filters['status_id']) ? (int) $filters['status_id'] : null);
        $query = $this->applyAssigneeFilter($query, isset($filters['assigned_to']) ? (int) $filters['assigned_to'] : null);

        return $query;
    }
}
