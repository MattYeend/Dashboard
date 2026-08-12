<?php

namespace App\Services\Tickets;

use App\Models\Ticket;
use App\Services\EscapesLikeValues;
use Illuminate\Database\Eloquent\Builder;

class FilterService
{
    use EscapesLikeValues;

    /**
     * Apply a search filter to the query.
     *
     * @param  Builder<Ticket>  $query
     * @return Builder<Ticket>
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
     * Filter by ticket status.
     *
     * @param  Builder<Ticket>  $query
     * @return Builder<Ticket>
     */
    public function applyStatus(Builder $query, ?int $ticketStatusId): Builder
    {
        if ($ticketStatusId === null) {
            return $query;
        }

        return $query->where('ticket_status_id', $ticketStatusId);
    }

    /**
     * Filter by ticket priority.
     *
     * @param  Builder<Ticket>  $query
     * @return Builder<Ticket>
     */
    public function applyPriority(Builder $query, ?int $ticketPriorityId): Builder
    {
        if ($ticketPriorityId === null) {
            return $query;
        }

        return $query->where('ticket_priority_id', $ticketPriorityId);
    }

    /**
     * Filter by assignee.
     *
     * @param  Builder<Ticket>  $query
     * @return Builder<Ticket>
     */
    public function applyAssignedTo(Builder $query, ?int $assignedTo): Builder
    {
        if ($assignedTo === null) {
            return $query;
        }

        return $query->where('assigned_to', $assignedTo);
    }

    /**
     * Filter by a due date range.
     *
     * @param  Builder<Ticket>  $query
     * @return Builder<Ticket>
     */
    public function applyDueDateRange(Builder $query, ?string $from, ?string $to): Builder
    {
        if ($from !== null) {
            $query->whereDate('due_date', '>=', $from);
        }

        if ($to !== null) {
            $query->whereDate('due_date', '<=', $to);
        }

        return $query;
    }

    /**
     * Filter by attached label.
     *
     * @param  Builder<Ticket>  $query
     * @return Builder<Ticket>
     */
    public function applyLabel(Builder $query, ?int $labelId): Builder
    {
        if ($labelId === null) {
            return $query;
        }

        return $query->whereHas('labels', function (Builder $q) use ($labelId): void {
            $q->where('labels.id', $labelId);
        });
    }

    /**
     * Apply all filters to the query.
     *
     * @param  Builder<Ticket>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<Ticket>
     */
    public function applyAll(Builder $query, array $filters): Builder
    {
        $query = $this->applySearch($query, $filters['search'] ?? null);
        $query = $this->applyStatus($query, $filters['ticket_status_id'] ?? null);
        $query = $this->applyPriority($query, $filters['ticket_priority_id'] ?? null);
        $query = $this->applyAssignedTo($query, $filters['assigned_to'] ?? null);
        $query = $this->applyDueDateRange($query, $filters['due_date_from'] ?? null, $filters['due_date_to'] ?? null);
        $query = $this->applyLabel($query, $filters['label_id'] ?? null);

        return $query;
    }
}
