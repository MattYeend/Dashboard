<?php

declare(strict_types=1);

namespace App\Services\Payments;

use App\Enums\PaymentMethod;
use App\Enums\PaymentType;
use App\Models\Payment;
use App\Services\EscapesLikeValues;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;

class FilterService
{
    use EscapesLikeValues;

    /**
     * Apply search filter (reference or invoice number) to query.
     *
     * @param  Builder<Payment>  $query
     * @return Builder<Payment>
     */
    public function applySearch(Builder $query, ?string $search): Builder
    {
        if ($search === null || $search === '') {
            return $query;
        }

        $search = $this->escapeLikeValue($search);

        return $query->where(function (Builder $q) use ($search): void {
            $q->where('reference', 'like', "%{$search}%")
                ->orWhereHas('invoice', function (Builder $invoice) use ($search): void {
                    $invoice->where('invoice_number', 'like', "%{$search}%");
                });
        });
    }

    /**
     * Apply invoice filter to query.
     *
     * @param  Builder<Payment>  $query
     * @return Builder<Payment>
     */
    public function applyInvoice(Builder $query, mixed $invoiceId): Builder
    {
        if (! is_scalar($invoiceId) || ! ctype_digit((string) $invoiceId)) {
            return $query;
        }

        return $query->where('invoice_id', (int) $invoiceId);
    }

    /**
     * Apply status filter to query.
     *
     * @param  Builder<Payment>  $query
     * @return Builder<Payment>
     */
    public function applyStatus(Builder $query, mixed $statusId): Builder
    {
        if (! is_scalar($statusId) || ! ctype_digit((string) $statusId)) {
            return $query;
        }

        return $query->where('status_id', (int) $statusId);
    }

    /**
     * Apply method filter to query. Unknown values are ignored.
     *
     * @param  Builder<Payment>  $query
     * @return Builder<Payment>
     */
    public function applyMethod(Builder $query, mixed $method): Builder
    {
        $enum = is_string($method) ? PaymentMethod::tryFrom($method) : null;

        return $enum === null ? $query : $query->where('method', $enum->value);
    }

    /**
     * Apply type filter to query. Unknown values are ignored.
     *
     * @param  Builder<Payment>  $query
     * @return Builder<Payment>
     */
    public function applyType(Builder $query, mixed $type): Builder
    {
        $enum = is_string($type) ? PaymentType::tryFrom($type) : null;

        return $enum === null ? $query : $query->where('type', $enum->value);
    }

    /**
     * Apply an inclusive paid_at date range (Y-m-d). Invalid dates are
     * ignored.
     *
     * @param  Builder<Payment>  $query
     * @return Builder<Payment>
     */
    public function applyDateRange(Builder $query, mixed $from, mixed $to): Builder
    {
        $start = $this->parseDate($from);
        $end = $this->parseDate($to);

        if ($start !== null) {
            $query->where('paid_at', '>=', $start->startOfDay());
        }

        if ($end !== null) {
            $query->where('paid_at', '<=', $end->endOfDay());
        }

        return $query;
    }

    /**
     * Apply all filters to query.
     *
     * @param  Builder<Payment>  $query
     * @param  array<string,mixed>  $filters
     * @return Builder<Payment>
     */
    public function applyAll(Builder $query, array $filters): Builder
    {
        $query = $this->applySearch($query, $filters['search'] ?? null);
        $query = $this->applyInvoice($query, $filters['invoice_id'] ?? null);
        $query = $this->applyStatus($query, $filters['status_id'] ?? null);
        $query = $this->applyMethod($query, $filters['method'] ?? null);
        $query = $this->applyType($query, $filters['type'] ?? null);

        return $this->applyDateRange($query, $filters['from'] ?? null, $filters['to'] ?? null);
    }

    /**
     * Parse a Y-m-d string, returning null when it is not a valid date.
     */
    protected function parseDate(mixed $value): ?CarbonImmutable
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        $date = CarbonImmutable::createFromFormat('!Y-m-d', $value);

        return $date === false ? null : $date;
    }
}
