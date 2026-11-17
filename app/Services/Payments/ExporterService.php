<?php

declare(strict_types=1);

namespace App\Services\Payments;

use App\Models\Log;
use App\Models\Payment;
use App\Services\AuditLogService;
use App\Services\TrashFilterService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExporterService
{
    /**
     * Columns written to the CSV, in order.
     *
     * @var array<int, string>
     */
    protected const COLUMNS = [
        'id',
        'invoice_number',
        'type',
        'status',
        'method',
        'amount',
        'currency',
        'reference',
        'paid_at',
        'parent_payment_id',
        'created_by',
        'created_at',
    ];

    /**
     * Inject the required services into the exporter service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
        protected readonly FilterService $filterService,
        protected readonly TrashFilterService $trashFilterService,
    ) {}

    /**
     * Stream all matching payments as a CSV download.
     *
     * @param  array<string, mixed>  $filters
     */
    public function export(array $filters): StreamedResponse
    {
        $query = Payment::query()->with(['invoice', 'status']);

        $query = $this->filterService->applyAll($query, $filters);

        /** @var Builder<Payment> $query */
        $query = $this->trashFilterService->applyFilter($query, $filters['trashed'] ?? null);

        $this->auditLogService->record(
            Log::ACTION_EXPORT_PAYMENT,
            Auth::user(),
            null,
            ['filters' => $filters, 'count' => (clone $query)->count()],
        );

        $callback = function () use ($query): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, self::COLUMNS);

            $query->orderBy('id')->chunk(500, function ($payments) use ($handle): void {
                foreach ($payments as $payment) {
                    /** @var Payment $payment */
                    $row = [
                        'id' => $payment->id,
                        'invoice_number' => $payment->invoice->invoice_number ?? null,
                        'type' => $payment->type->value,
                        'status' => $payment->status->title ?? null,
                        'method' => $payment->method->value,
                        'amount' => $payment->amount,
                        'currency' => $payment->currency,
                        'reference' => $payment->reference,
                        'paid_at' => $payment->paid_at->toIso8601String(),
                        'parent_payment_id' => $payment->parent_payment_id,
                        'created_by' => $payment->created_by,
                        'created_at' => $payment->created_at->toIso8601String(),
                    ];

                    fputcsv($handle, array_map($this->sanitiseCell(...), $row));
                }
            });

            fclose($handle);
        };

        return response()->streamDownload(
            $callback,
            'payments-'.now()->format('Y-m-d-His').'.csv',
            ['Content-Type' => 'text/csv']
        );
    }

    /**
     * Neutralise spreadsheet formula injection in a single cell.
     */
    private function sanitiseCell(mixed $value): mixed
    {
        if (! is_string($value) || $value === '') {
            return $value;
        }

        return in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)
            ? "'".$value
            : $value;
    }
}
