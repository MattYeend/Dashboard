<?php

declare(strict_types=1);

namespace App\Services\QuoteItems;

use App\Models\Log;
use App\Models\Quote;
use App\Models\QuoteItem;
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
        'position',
        'sku',
        'name',
        'description',
        'unit',
        'quantity',
        'unit_price',
        'discount_percent',
        'tax_rate',
        'line_total',
        'line_tax',
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
     * Stream all matching lines of one quote as a CSV download.
     *
     * @param  array<string, mixed>  $filters
     */
    public function export(array $filters, Quote $quote): StreamedResponse
    {
        $query = QuoteItem::query()->with('product');

        $query = $this->filterService->forQuote($query, $quote->id);
        $query = $this->filterService->applySearch($query, $filters['search'] ?? null);
        $query = $this->trashFilterService->applyFilter($query, $filters['trashed'] ?? null);

        $this->auditLogService->record(
            Log::ACTION_EXPORT_QUOTE_ITEM,
            Auth::user(),
            $quote,
            ['filters' => $filters, 'count' => (clone $query)->count()],
        );

        $callback = function () use ($query): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, self::COLUMNS);

            $query->orderBy('position')->orderBy('id')->chunk(500, function ($items) use ($handle): void {
                foreach ($items as $item) {
                    $row = $item->only(self::COLUMNS);
                    $row['sku'] = $item->product?->sku;

                    fputcsv($handle, array_map($this->sanitiseCell(...), $row));
                }
            });

            fclose($handle);
        };

        return response()->streamDownload(
            $callback,
            'quote-'.$quote->quote_number.'-lines-'.now()->format('Y-m-d-His').'.csv',
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