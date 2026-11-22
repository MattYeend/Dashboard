<?php

declare(strict_types=1);

namespace App\Services\Campaigns;

use App\Models\Campaign;
use App\Models\Log;
use App\Services\AuditLogService;
use DateTimeInterface;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExporterService
{
    /**
     * Columns written to the CSV, in order.
     *
     * @var array<int, string>
     */
    public const COLUMNS = [
        'id',
        'name',
        'email_template_id',
        'campaign_segment_id',
        'owner_id',
        'subject',
        'from_name',
        'reply_to',
        'scheduled_at',
        'started_at',
        'completed_at',
        'cancelled_at',
        'recipients_count',
        'sent_count',
        'failed_count',
        'skipped_count',
        'opened_count',
        'unsubscribed_count',
        'status_id',
        'created_at',
    ];

    /**
     * Characters that make a spreadsheet treat a cell as a formula.
     *
     * @var array<int, string>
     */
    private const FORMULA_PREFIXES = ['=', '+', '-', '@', "\t", "\r"];

    /**
     * Inject the required services into the exporter service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
        protected readonly FilterService $filterService,
    ) {}

    /**
     * Stream all matching campaigns as a CSV download.
     *
     * @param  array<string, mixed>  $filters
     */
    public function export(array $filters): StreamedResponse
    {
        $query = $this->filterService->applyAll(Campaign::query(), $filters);

        $trashed = $filters['trashed'] ?? null;

        if ($trashed === 'with') {
            $query->withTrashed();
        } elseif ($trashed === 'only') {
            $query->onlyTrashed();
        }

        $this->auditLogService->record(
            Log::ACTION_EXPORT_CAMPAIGN,
            Auth::user(),
            null,
            ['filters' => $filters, 'count' => (clone $query)->count()],
        );

        $callback = function () use ($query): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, self::COLUMNS);

            $query->orderBy('id')->chunk(500, function ($campaigns) use ($handle): void {
                foreach ($campaigns as $campaign) {
                    fputcsv($handle, $this->row($campaign));
                }
            });

            fclose($handle);
        };

        return response()->streamDownload(
            $callback,
            'campaigns-'.now()->format('Y-m-d-His').'.csv',
            ['Content-Type' => 'text/csv']
        );
    }

    /**
     * Build a sanitised CSV row for a campaign.
     *
     * @return array<int, mixed>
     */
    private function row(Campaign $campaign): array
    {
        $row = [];

        foreach (self::COLUMNS as $column) {
            $value = $campaign->getAttribute($column);

            if ($value instanceof DateTimeInterface) {
                $value = $value->format('Y-m-d H:i:s');
            }

            $row[] = $this->sanitiseCell($value);
        }

        return $row;
    }

    /**
     * Prefix formula trigger characters with a quote to prevent
     * spreadsheet formula injection (OWASP CSV injection, ISO 27001 A.8.28).
     */
    private function sanitiseCell(mixed $value): mixed
    {
        if (! is_string($value) || $value === '') {
            return $value;
        }

        return in_array($value[0], self::FORMULA_PREFIXES, true)
            ? "'".$value
            : $value;
    }
}
