<?php

namespace App\Services\Orders;

use App\Models\Log;
use App\Models\Order;
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
     * Stream all matching orders as a CSV download.
     *
     * @param  array<string, mixed>  $filters
     */
    public function export(array $filters): StreamedResponse
    {
        $query = Order::query()->with(['orderable', 'status']);

        if (! empty($filters['trashed']) && $filters['trashed'] === 'with') {
            $query->withTrashed();
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function ($inner) use ($search) {
                $inner->where('order_number', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%");
            });
        }

        $columns = [
            'id',
            'orderable_type',
            'orderable_id',
            'order_number',
            'title',
            'description',
            'subtotal',
            'discount_amount',
            'tax_amount',
            'total_amount',
            'ordered_at',
            'due_at',
            'completed_at',
            'status_id',
            'created_by',
            'created_at',
        ];

        $this->auditLogService->record(
            Log::ACTION_EXPORT_ORDER,
            Auth::user(),
            null,
            ['filters' => $filters, 'count' => (clone $query)->count()],
        );

        $callback = function () use ($query, $columns) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $columns);

            $query->orderBy('id')->chunk(500, function ($orders) use ($handle, $columns) {
                foreach ($orders as $order) {
                    fputcsv($handle, $order->only($columns));
                }
            });

            fclose($handle);
        };

        return response()->streamDownload(
            $callback,
            'orders-'.now()->format('Y-m-d-His').'.csv',
            ['Content-Type' => 'text/csv']
        );
    }
}
