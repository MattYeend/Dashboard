<?php

namespace App\Services\Invoices;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Builder;

class SortingService
{
    /**
     * Apply sorting to the query.
     *
     * @param  Builder<Invoice>  $query
     * @return Builder<Invoice>
     */
    public function applySorting(
        Builder $query,
        ?string $sortBy = 'created_at',
        ?string $sortDirection = 'asc'
    ): Builder {
        $sortBy = $sortBy ?? 'created_at';
        $sortDirection = strtolower($sortDirection ?? 'asc') === 'asc' ? 'asc' : 'desc';

        return match ($sortBy) {
            'invoice_number' => $query->orderBy('invoice_number', $sortDirection),
            'issue_date' => $query->orderBy('issue_date', $sortDirection),
            'due_date' => $query->orderBy('due_date', $sortDirection),
            'total' => $query->orderBy('total', $sortDirection),
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
            'invoice_number' => 'Invoice Number',
            'issue_date' => 'Issue Date',
            'due_date' => 'Due Date',
            'total' => 'Total',
            'created_at' => 'Created Date',
        ];
    }
}
