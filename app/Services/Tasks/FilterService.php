<?php

namespace App\Services\Tasks;

use App\Models\Task;
use App\Services\EscapesLikeValues;
use Illuminate\Database\Eloquent\Builder;

class FilterService
{
    use EscapesLikeValues;

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

        $search = $this->escapeLikeValue($search);

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

        return $query->where(
            'status_id',
            $statusId
        );
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

        return $query->where(
            'assigned_to',
            $assignedTo
        );
    }

    /**
     * Apply due date filter to the query.
     *
     * @param  Builder<Task>  $query
     * @return Builder<Task>
     */
    public function applyDueDateFilter(Builder $query, ?string $dueDate): Builder
    {
        if ($dueDate === null) {
            return $query;
        }

        return $query->whereDate(
            'due_date',
            $dueDate
        );
    }

    /**
     * Apply assigned date filter to the query.
     *
     * @param  Builder<Task>  $query
     * @return Builder<Task>
     */
    public function applyAssignedDateFilter(Builder $query, ?string $assignedDate): Builder
    {
        if ($assignedDate === null) {
            return $query;
        }

        return $query->whereDate(
            'assigned_date',
            $assignedDate
        );
    }

    /**
     * Apply a tag filter to the query.
     *
     * @param  Builder<Task>  $query
     * @return Builder<Task>
     */
    public function applyTagFilter(Builder $query, int|string|null $tagId): Builder
    {
        if ($tagId === null || $tagId === '') {
            return $query;
        }

        return $query->whereHas('tags', function (Builder $q) use ($tagId): void {
            $q->where('tags.id', $tagId);
        });
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
        $query = $this->applyDueDateFilter($query, $filters['due_date'] ?? null);
        $query = $this->applyAssignedDateFilter($query, $filters['assigned_date'] ?? null);
        $query = $this->applyTagFilter($query, $filters['tag_id'] ?? null);

        return $query;
    }
}
