<?php

declare(strict_types=1);

namespace App\Services\Products;

use App\Models\Log;
use App\Models\Product;
use App\Services\AuditLogService;
use App\Services\TrashFilterService;
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
        'sku',
        'name',
        'description',
        'unit',
        'unit_price',
        'cost_price',
        'tax_rate',
        'currency',
        'is_active',
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
     * Stream all matching products as a CSV download.
     *
     * @param  array<string, mixed>  $filters
     */
    public function export(array $filters): StreamedResponse
    {
        $query = Product::query();

        $query = $this->filterService->applySearch($query, $filters['search'] ?? null);
        $query = $this->trashFilterService->applyFilter($query, $filters['trashed'] ?? null);

        $this->auditLogService->record(
            Log::ACTION_EXPORT_PRODUCT,
            Auth::user(),
            null,
            ['filters' => $filters, 'count' => (clone $query)->count()],
        );

        $callback = function () use ($query): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, self::COLUMNS);

            $query->orderBy('id')->chunk(500, function ($products) use ($handle): void {
                foreach ($products as $product) {
                    $row = $product->only(self::COLUMNS);
                    $row['is_active'] = $product->is_active ? '1' : '0';

                    fputcsv($handle, array_map($this->sanitiseCell(...), $row));
                }
            });

            fclose($handle);
        };

        return response()->streamDownload(
            $callback,
            'products-'.now()->format('Y-m-d-His').'.csv',
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