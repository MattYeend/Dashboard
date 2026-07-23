<?php

namespace App\Services\Invoices;

use App\Models\Invoice;
use App\Services\EscapesLikeValues;
use Illuminate\Database\Eloquent\Builder;

class FilterService
{
    use EscapesLikeValues;

    /**
     * Apply a search filter to the query.
     *
     * @param  Builder<Invoice>  $query
     * @return Builder<Invoice>
     */
    public function applySearch(
        Builder $query,
        ?string $search
    ): Builder {
        if ($search === null) {
            return $query;
        }

        $search = $this->escapeLikeValue($search);

        return $query->where(function (Builder $q) use ($search): void {
            $q->where('invoice_number', 'like', "%{$search}%")
                ->orWhere('notes', 'like', "%{$search}%");
        });
    }

    /**
     * Apply status filter to the query.
     *
     * @param  Builder<Invoice>  $query
     * @return Builder<Invoice>
     */
    public function applyStatusFilter(
        Builder $query,
        ?int $statusId
    ): Builder {
        if ($statusId === null) {
            return $query;
        }

        return $query->where(
            'status_id',
            $statusId
        );
    }

    /**
     * Apply company filter to the query.
     *
     * @param  Builder<Invoice>  $query
     * @return Builder<Invoice>
     */
    public function applyCompanyFilter(
        Builder $query,
        ?int $companyId
    ): Builder {
        if ($companyId === null) {
            return $query;
        }

        return $query->where(
            'company_id',
            $companyId
        );
    }

    /**
     * Apply contact filter to the query.
     *
     * Contacts are polymorphic (contactable_type/contactable_id), so this
     * can no longer filter on a contact_id column directly - it must go
     * through the contact() relation.
     *
     * @param  Builder<Invoice>  $query
     * @return Builder<Invoice>
     */
    public function applyContactFilter(
        Builder $query,
        ?int $contactId
    ): Builder {
        if ($contactId === null) {
            return $query;
        }

        return $query->whereHas('contact', function (Builder $q) use ($contactId): void {
            $q->whereKey(
                $contactId
            );
        });
    }

    /**
     * Apply order filter to the query.
     *
     * @param  Builder<Invoice>  $query
     * @return Builder<Invoice>
     */
    public function applyOrderFilter(
        Builder $query,
        ?int $orderId
    ): Builder {
        if ($orderId === null) {
            return $query;
        }

        return $query->where(
            'order_id',
            $orderId
        );
    }

    /**
     * Apply a due date range filter to the query.
     *
     * @param  Builder<Invoice>  $query
     * @return Builder<Invoice>
     */
    public function applyDueDateRangeFilter(
        Builder $query,
        ?string $dueDateFrom,
        ?string $dueDateTo
    ): Builder {
        if ($dueDateFrom !== null) {
            $query->whereDate(
                'due_date',
                '>=',
                $dueDateFrom
            );
        }

        if ($dueDateTo !== null) {
            $query->whereDate(
                'due_date',
                '<=',
                $dueDateTo
            );
        }

        return $query;
    }

    /**
     * Apply all filters to the query.
     *
     * @param  Builder<Invoice>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<Invoice>
     */
    public function applyAll(
        Builder $query,
        array $filters
    ): Builder {
        $query = $this->applySearch($query, $filters['search'] ?? null);
        $query = $this->applyStatusFilter($query, isset($filters['status_id']) ? (int) $filters['status_id'] : null);
        $query = $this->applyCompanyFilter($query, isset($filters['company_id']) ? (int) $filters['company_id'] : null);
        $query = $this->applyContactFilter($query, isset($filters['contact_id']) ? (int) $filters['contact_id'] : null);
        $query = $this->applyOrderFilter($query, isset($filters['order_id']) ? (int) $filters['order_id'] : null);
        $query = $this->applyDueDateRangeFilter(
            $query,
            $filters['due_date_from'] ?? null,
            $filters['due_date_to'] ?? null
        );

        return $query;
    }
}
