<?php

declare(strict_types=1);

namespace App\Services\EmailSuppressions;

use App\Models\EmailSuppression;
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
        'email',
        'reason',
        'campaign_id',
        'contact_id',
        'suppressed_at',
        'notes',
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
     * Stream all matching email suppressions as a CSV download.
     *
     * @param  array<string, mixed>  $filters
     */
    public function export(array $filters): StreamedResponse
    {
        $query = $this->filterService->applyAll(EmailSuppression::query(), $filters);

        $trashed = $filters['trashed'] ?? null;

        if ($trashed === 'with') {
            $query->withTrashed();
        } elseif ($trashed === 'only') {
            $query->onlyTrashed();
        }

        $this->auditLogService->record(
            Log::ACTION_EXPORT_EMAIL_SUPPRESSION,
            Auth::user(),
            null,
            ['filters' => $filters, 'count' => (clone $query)->count()],
        );

        $callback = function () use ($query): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, self::COLUMNS);

            $query->orderBy('id')->chunk(500, function ($emailSuppressions) use ($handle): void {
                foreach ($emailSuppressions as $emailSuppression) {
                    fputcsv($handle, $this->row($emailSuppression));
                }
            });

            fclose($handle);
        };

        return response()->streamDownload(
            $callback,
            'email-suppressions-'.now()->format('Y-m-d-His').'.csv',
            ['Content-Type' => 'text/csv']
        );
    }

    /**
     * Build a sanitised CSV row for an email suppression.
     *
     * @return array<int, mixed>
     */
    private function row(EmailSuppression $emailSuppression): array
    {
        $row = [];

        foreach (self::COLUMNS as $column) {
            $value = $emailSuppression->getAttribute($column);

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
