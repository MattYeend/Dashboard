<?php

declare(strict_types=1);

namespace App\Services\CampaignRecipients;

use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Models\Log;
use App\Services\AuditLogService;
use DateTimeInterface;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExporterService
{
    /**
     * Columns written to the CSV, in order. The token is never exported.
     *
     * @var array<int, string>
     */
    public const COLUMNS = [
        'id',
        'campaign_id',
        'contact_id',
        'email',
        'name',
        'status',
        'attempts',
        'sent_at',
        'opened_at',
        'clicked_at',
        'unsubscribed_at',
        'failed_at',
        'failure_reason',
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
     * Stream all matching campaign recipients of a campaign as a CSV download.
     *
     * @param  array<string, mixed>  $filters
     */
    public function export(Campaign $campaign, array $filters): StreamedResponse
    {
        $query = $this->filterService->applyAll(
            CampaignRecipient::query()->where('campaign_id', $campaign->id),
            $filters
        );

        $trashed = $filters['trashed'] ?? null;

        if ($trashed === 'with') {
            $query->withTrashed();
        } elseif ($trashed === 'only') {
            $query->onlyTrashed();
        }

        $this->auditLogService->record(
            Log::ACTION_EXPORT_CAMPAIGN_RECIPIENT,
            Auth::user(),
            null,
            [
                'campaign_id' => $campaign->id,
                'filters' => $filters,
                'count' => (clone $query)->count(),
            ],
        );

        $callback = function () use ($query): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, self::COLUMNS);

            $query->orderBy('id')->chunk(500, function ($campaignRecipients) use ($handle): void {
                foreach ($campaignRecipients as $campaignRecipient) {
                    fputcsv($handle, $this->row($campaignRecipient));
                }
            });

            fclose($handle);
        };

        return response()->streamDownload(
            $callback,
            'campaign-recipients-'.now()->format('Y-m-d-His').'.csv',
            ['Content-Type' => 'text/csv']
        );
    }

    /**
     * Build a sanitised CSV row for a campaign recipient.
     *
     * @return array<int, mixed>
     */
    private function row(CampaignRecipient $campaignRecipient): array
    {
        $row = [];

        foreach (self::COLUMNS as $column) {
            $value = $campaignRecipient->getAttribute($column);

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