<?php

namespace App\Services\Tickets;

use App\Models\Ticket;
use Illuminate\Database\Eloquent\Builder;

class SortingService
{
    /**
     * Apply sorting to the query.
     *
     * @param  Builder<Ticket>  $query
     * @return Builder<Ticket>
     */
    public function applySorting(
        Builder $query,
        ?string $sortBy = 'created_at',
        ?string $sortDirection = 'desc'
    ): Builder {
        $sortBy = $sortBy ?? 'created_at';
        $sortDirection = strtolower($sortDirection ?? 'asc') === 'asc' ? 'asc' : 'desc';

        return match ($sortBy) {
            'title' => $query->orderBy('title', $sortDirection),
            'status' => $query->orderBy('ticket_status_id', $sortDirection),
            'priority' => $query->orderBy('ticket_priority_id', $sortDirection),
            'due_date' => $query->orderBy('due_date', $sortDirection),
            'resolved_at' => $query->orderBy('resolved_at', $sortDirection),
            'updated_at' => $query->orderBy('updated_at', $sortDirection),
            default => $query->orderBy('created_at', $sortDirection),
        };
    }

    /**
     * Get available sort fields.
     *
     * @return array<string, string>
     */
    public function getAvailableSortFields(): array
    {
        return [
            'title' => 'Title',
            'status' => 'Status',
            'priority' => 'Priority',
            'due_date' => 'Due Date',
            'resolved_at' => 'Resolved Date',
            'created_at' => 'Created Date',
        ];
    }
}
