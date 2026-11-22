<?php

declare(strict_types=1);

namespace App\Services\CampaignSegments;

use App\Models\CampaignSegment;
use App\Models\Log;
use App\Services\AuditLogService;
use BackedEnum;
use DateTimeInterface;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExporterService
{
    /**
     * Columns written to the CSV. criteria is not exported.
     *
     * @var array<int, string>
     */
    public const COLUMNS = [
        'id',
        'name',
        'description',
        'estimated_recipients_count',
        'last_refreshed_at',
        'created_at',
    ];

    /**
     * Inject the required services into the exporter service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
        protected readonly FilterService $filterService,
    ) {}

    /**
     * Stream all matching campaign segments as a CSV download.
     *
     * @param  array<string, mixed>  $filters
     */
    public function export(array $filters): StreamedResponse
    {
        $filters = $this->filterService->normalise($filters);

        $query = CampaignSegment::query();

        if (($filters['trashed'] ?? null) === 'with') {
            $query->withTrashed();
        }

        $query = $this->filterService->applyAll($query, $filters);

        $this->auditLogService->record(
            Log::ACTION_EXPORT_CAMPAIGN_SEGMENT,
            Auth::user(),
            null,
            ['filters' => $filters, 'count' => (clone $query)->count()],
        );

        $callback = function () use ($query): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, self::COLUMNS, ',', '"', '');

            $query->chunkById(500, function ($campaignSegments) use ($handle): void {
                foreach ($campaignSegments as $campaignSegment) {
                    fputcsv($handle, array_map(
                        fn (mixed $value): mixed => $this->sanitiseCell($value),
                        $campaignSegment->only(self::COLUMNS)
                    ), ',', '"', '');
                }
            });

            fclose($handle);
        };

        return response()->streamDownload(
            $callback,
            'campaign-segments-'.now()->format('Y-m-d-His').'.csv',
            ['Content-Type' => 'text/csv']
        );
    }

    /**
     * Normalise a value for CSV output and neutralise spreadsheet formula injection.
     */
    private function sanitiseCell(mixed $value): mixed
    {
        if ($value instanceof BackedEnum) {
            $value = $value->value;
        }

        if ($value instanceof DateTimeInterface) {
            $value = $value->format('Y-m-d H:i:s');
        }

        if (! is_string($value) || $value === '') {
            return $value;
        }

        return in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)
            ? "'".$value
            : $value;
    }
}