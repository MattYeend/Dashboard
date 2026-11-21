<?php

declare(strict_types=1);

namespace App\Services\EmailTemplates;

use App\Models\EmailTemplate;
use App\Models\Log;
use App\Services\AuditLogService;
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
        'subject',
        'preheader',
        'is_active',
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
     * Stream all matching email templates as a CSV download.
     *
     * @param  array<string, mixed>  $filters
     */
    public function export(array $filters): StreamedResponse
    {
        $query = $this->filterService->applyAll(EmailTemplate::query(), $filters);

        $trashed = $filters['trashed'] ?? null;

        if ($trashed === 'with') {
            $query->withTrashed();
        } elseif ($trashed === 'only') {
            $query->onlyTrashed();
        }

        $this->auditLogService->record(
            Log::ACTION_EXPORT_EMAIL_TEMPLATE,
            Auth::user(),
            null,
            ['filters' => $filters, 'count' => (clone $query)->count()],
        );

        $callback = function () use ($query): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, self::COLUMNS);

            $query->orderBy('id')->chunk(500, function ($emailTemplates) use ($handle): void {
                foreach ($emailTemplates as $emailTemplate) {
                    fputcsv($handle, $this->row($emailTemplate));
                }
            });

            fclose($handle);
        };

        return response()->streamDownload(
            $callback,
            'email-templates-'.now()->format('Y-m-d-His').'.csv',
            ['Content-Type' => 'text/csv']
        );
    }

    /**
     * Build a sanitised CSV row for an email template.
     *
     * @return array<int, mixed>
     */
    private function row(EmailTemplate $emailTemplate): array
    {
        return array_map($this->sanitiseCell(...), [
            $emailTemplate->id,
            $emailTemplate->name,
            $emailTemplate->subject,
            $emailTemplate->preheader,
            $emailTemplate->is_active ? 1 : 0,
            $emailTemplate->created_at->format('Y-m-d H:i:s'),
        ]);
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
