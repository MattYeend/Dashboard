<?php

namespace App\Services\Dashboard;

use App\Models\Invoice;

class InvoiceStatsService
{
    /**
     * Get total, paid and outstanding invoice counts.
     *
     * Paid/outstanding is determined by InvoiceStatus title: "Paid"
     * counts as paid, "Pending", "Sent" and "Overdue" count as
     * outstanding.
     *
     * @return array{total: int, paid: int, outstanding: int}
     */
    public function summary(): array
    {
        $total = Invoice::query()->count();

        $paid = Invoice::query()
            ->whereHas('status', fn ($query) => $query->where('title', 'Paid'))
            ->count();

        $outstanding = Invoice::query()
            ->whereHas('status', fn ($query) => $query->whereIn('title', [
                'Pending', 'Sent', 'Overdue',
            ]))
            ->count();

        return [
            'total' => $total,
            'paid' => $paid,
            'outstanding' => $outstanding,
        ];
    }
}
