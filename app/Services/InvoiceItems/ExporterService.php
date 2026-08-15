<?php

namespace App\Services\InvoiceItems;

use App\Models\Invoice;
use App\Models\Log;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExporterService
{
    /**
     * Inject the audit log service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
    ) {}

    /**
     * Stream all items belonging to a single invoice as a CSV download.
     *
     * @param  array<string, mixed>  $filters
     */
    public function export(Invoice $invoice, array $filters): StreamedResponse
    {
        $query = $invoice->items();

        if (! empty($filters['trashed']) && $filters['trashed'] === 'with') {
            $query->withTrashed();
        }

        $columns = [
            'id',
            'invoice_id',
            'description',
            'quantity',
            'unit_price',
            'tax_rate',
            'total',
            'position',
            'created_at',
        ];

        $this->auditLogService->record(
            Log::ACTION_EXPORT_INVOICE_ITEM,
            Auth::user(),
            null,
            ['invoice_id' => $invoice->id, 'filters' => $filters, 'count' => (clone $query)->count()],
        );

        $callback = function () use ($query, $columns) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $columns);

            $query->orderBy('position')->chunk(500, function ($items) use ($handle, $columns) {
                foreach ($items as $item) {
                    fputcsv($handle, $item->only($columns));
                }
            });

            fclose($handle);
        };

        return response()->streamDownload(
            $callback,
            'invoice-'.$invoice->id.'-items-'.now()->format('Y-m-d-His').'.csv',
            ['Content-Type' => 'text/csv']
        );
    }
}
