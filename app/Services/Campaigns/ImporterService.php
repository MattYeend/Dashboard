<?php

declare(strict_types=1);

namespace App\Services\Campaigns;

use App\Models\Campaign;
use App\Models\Log;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\Concerns\ImportsViaPreview;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Handles CSV import of campaigns, including the preview/commit workflow
 * (via ImportsViaPreview) alongside the one-shot import() method.
 *
 * Imported campaigns are always created as drafts. Template, segment and
 * owner are not importable, so no foreign keys cross a tenant boundary.
 */
class ImporterService
{
    use ImportsViaPreview;

    /**
     * Column headers that must be present in the uploaded CSV.
     *
     * @var array<int, string>
     */
    protected const REQUIRED_COLUMNS = [
        'name',
    ];

    /**
     * Column headers that may be present in the uploaded CSV.
     *
     * @var array<int, string>
     */
    protected const OPTIONAL_COLUMNS = [
        'subject',
        'from_name',
        'reply_to',
        'scheduled_at',
    ];

    /**
     * Maximum length of the varchar columns.
     */
    protected const MAX_STRING_LENGTH = 255;

    /**
     * Inject the required services into the importer service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
        protected readonly DataPreparationService $dataPreparation,
    ) {}

    /**
     * The private-disk directory this module's uploads are stored under.
     */
    protected function importStoragePath(): string
    {
        return 'imports/campaigns';
    }

    /**
     * The Log::ACTION_IMPORT_* constant for this module's batch log entry.
     */
    protected function importAuditAction(): int
    {
        return Log::ACTION_IMPORT_CAMPAIGN;
    }

    /**
     * Persist a single validated row as a new Campaign.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $context
     */
    protected function persistRow(array $data, int $actorId, array $context = []): void
    {
        $this->createFromRow($data, $actorId);
    }

    /**
     * Import campaigns from an uploaded CSV file.
     *
     * @return array{imported: int, skipped: array<int, array{row: int, reason: string}>}
     */
    public function import(
        UploadedFile $file,
        int $actorId
    ): array {
        $handle = fopen($file->getRealPath(), 'r');

        if ($handle === false) {
            return [
                'imported' => 0,
                'skipped' => [['row' => 0, 'reason' => 'The uploaded file could not be read']],
            ];
        }

        $header = fgetcsv($handle);
        $header = array_map(
            fn (string $column) => strtolower(trim(str_replace("\xEF\xBB\xBF", '', $column))),
            $header ?: []
        );

        $missing = array_diff(self::REQUIRED_COLUMNS, $header);

        if (! empty($missing)) {
            fclose($handle);

            return [
                'imported' => 0,
                'skipped' => [[
                    'row' => 0,
                    'reason' => 'Missing required column(s): '.implode(', ', $missing),
                ]],
            ];
        }

        $imported = 0;
        $skipped = [];
        $rowNumber = 1;
        $actor = User::findOrFail($actorId);

        DB::transaction(function () use ($handle, $header, $actor, $actorId, &$imported, &$skipped, &$rowNumber): void {
            while (($row = fgetcsv($handle)) !== false) {
                $rowNumber++;

                if (count($row) !== count($header)) {
                    $skipped[] = [
                        'row' => $rowNumber,
                        'reason' => sprintf(
                            'Expected %d columns but found %d',
                            count($header),
                            count($row),
                        ),
                    ];

                    continue;
                }

                $data = array_combine($header, $row);

                $error = $this->validateRow($data);

                if ($error !== null) {
                    $skipped[] = ['row' => $rowNumber, 'reason' => $error];

                    continue;
                }

                $campaign = $this->createFromRow($data, $actorId);

                $this->auditLogService->record(
                    Log::ACTION_IMPORT_CAMPAIGN,
                    $actor,
                    $campaign,
                    ['after' => $this->auditLogService->snapshot($campaign)],
                );

                $imported++;
            }
        });

        fclose($handle);

        return ['imported' => $imported, 'skipped' => $skipped];
    }

    /**
     * Validate a single row, returning a human readable error or null if valid.
     *
     * @param  array<string, mixed>  $data
     */
    protected function validateRow(array $data): ?string
    {
        foreach (self::REQUIRED_COLUMNS as $column) {
            if (! isset($data[$column]) || trim((string) $data[$column]) === '') {
                return "Missing value for '{$column}'";
            }
        }

        foreach (['name', 'subject', 'from_name', 'reply_to'] as $column) {
            $value = isset($data[$column]) ? (string) $data[$column] : '';

            if (mb_strlen($value) > self::MAX_STRING_LENGTH) {
                return "'{$column}' must not exceed ".self::MAX_STRING_LENGTH.' characters';
            }

            if (preg_match('/[\x00-\x1F\x7F]/u', $value) === 1) {
                return "'{$column}' must not contain line breaks or control characters";
            }
        }

        $replyTo = trim((string) ($data['reply_to'] ?? ''));

        if ($replyTo !== '' && filter_var($replyTo, FILTER_VALIDATE_EMAIL) === false) {
            return "'reply_to' must be a valid email address";
        }

        $scheduledAt = trim((string) ($data['scheduled_at'] ?? ''));

        if ($scheduledAt !== '') {
            try {
                Carbon::parse($scheduledAt);
            } catch (\Throwable) {
                return "'scheduled_at' must be a valid date and time (e.g. 2026-12-01 09:00)";
            }
        }

        return null;
    }

    /**
     * Trim values, convert empty strings to null and map the CSV row to
     * the attributes expected by DataPreparationService.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function attributesFromRow(array $data): array
    {
        $clean = static function (mixed $value): ?string {
            if ($value === null) {
                return null;
            }

            $trimmed = trim((string) $value);

            return $trimmed === '' ? null : $trimmed;
        };

        return [
            'name' => $clean($data['name'] ?? null),
            'subject' => $clean($data['subject'] ?? null),
            'from_name' => $clean($data['from_name'] ?? null),
            'reply_to' => $clean($data['reply_to'] ?? null),
            'scheduled_at' => $clean($data['scheduled_at'] ?? null),
        ];
    }

    /**
     * Create a campaign from a validated row and stamp the creator.
     *
     * @param  array<string, mixed>  $data
     */
    protected function createFromRow(array $data, int $actorId): Campaign
    {
        $attributes = $this->dataPreparation->prepareForCreation(
            $this->attributesFromRow($data)
        );

        $campaign = Campaign::create($attributes);

        $campaign->forceFill(['created_by' => $actorId])->save();

        return $campaign;
    }
}