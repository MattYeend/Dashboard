<?php

declare(strict_types=1);

namespace App\Services\QuoteStatuses;

use App\Models\Log;
use App\Models\QuoteStatus;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\Concerns\ImportsViaPreview;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * Handles CSV import of quote statuses. Imported statuses are always
 * custom statuses, never system statuses.
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
        'title',
    ];

    /**
     * Inject the audit log service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
    ) {}

    /**
     * The private-disk directory this module's uploads are stored under.
     */
    protected function importStoragePath(): string
    {
        return 'imports/quote-statuses';
    }

    /**
     * The Log::ACTION_IMPORT_* constant for this module's batch log entry.
     */
    protected function importAuditAction(): int
    {
        return Log::ACTION_IMPORT_QUOTE_STATUS;
    }

    /**
     * Persist a single validated row as a new QuoteStatus.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $context
     */
    protected function persistRow(array $data, int $actorId, array $context = []): void
    {
        $title = trim((string) $data['title']);

        if (QuoteStatus::withTrashed()->where('title', $title)->exists()) {
            return;
        }

        QuoteStatus::create($this->attributesFromRow($data, $actorId));
    }

    /**
     * Import quote statuses from an uploaded CSV file.
     *
     * @return array{imported: int, skipped: array<int, array{row: int, reason: string}>}
     */
    public function import(
        UploadedFile $file,
        int $actorId
    ): array {
        $handle = fopen($file->getRealPath(), 'r');

        $header = fgetcsv($handle);
        $header = array_map(fn (?string $column) => strtolower(trim((string) $column)), $header ?: []);

        $missing = array_diff(self::REQUIRED_COLUMNS, $header);

        if ($missing !== []) {
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

        DB::transaction(function () use ($handle, $header, $actor, $actorId, &$imported, &$skipped, &$rowNumber) {
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

                $quoteStatus = QuoteStatus::create($this->attributesFromRow($data, $actorId));

                $this->auditLogService->record(
                    Log::ACTION_IMPORT_QUOTE_STATUS,
                    $actor,
                    $quoteStatus,
                    ['after' => $this->auditLogService->snapshot($quoteStatus)],
                );

                $imported++;
            }
        });

        fclose($handle);

        return ['imported' => $imported, 'skipped' => $skipped];
    }

    /**
     * Validate a single row, returning an error string or null if valid.
     *
     * @param  array<string, mixed>  $data
     */
    protected function validateRow(array $data): ?string
    {
        $title = trim((string) ($data['title'] ?? ''));

        if ($title === '') {
            return "Missing value for 'title'";
        }

        if (mb_strlen($title) > 255) {
            return 'title may not exceed 255 characters';
        }

        if (QuoteStatus::withTrashed()->where('title', $title)->exists()) {
            return "A quote status titled '{$title}' already exists";
        }

        foreach (['background_colour', 'text_colour'] as $column) {
            $value = trim((string) ($data[$column] ?? ''));

            if ($value !== '' && ! preg_match('/^#[0-9A-Fa-f]{6}$/', $value)) {
                return "{$column} must be a hex value such as #e2e8f0";
            }
        }

        return null;
    }

    /**
     * Build the attributes to persist from a validated row.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function attributesFromRow(array $data, int $actorId): array
    {
        $background = trim((string) ($data['background_colour'] ?? ''));
        $text = trim((string) ($data['text_colour'] ?? ''));
        $description = trim((string) ($data['description'] ?? ''));

        return [
            'title' => trim((string) $data['title']),
            'description' => $description === '' ? null : $description,
            'background_colour' => $background === '' ? '#ffffff' : strtolower($background),
            'text_colour' => $text === '' ? '#000000' : strtolower($text),
            'created_by' => $actorId,
        ];
    }
}