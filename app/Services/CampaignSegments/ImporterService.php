<?php

declare(strict_types=1);

namespace App\Services\CampaignSegments;

use App\Models\CampaignSegment;
use App\Models\Log;
use App\Models\Organisation;
use App\Models\User;
use App\Services\Concerns\ImportsViaPreview;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

/**
 * Handles CSV import of campaign segments, including the preview/commit
 * workflow (via ImportsViaPreview) alongside the one-shot import() method.
 */
class ImporterService
{
    use ImportsViaPreview;

    /**
     * Column headers that must be present in the uploaded CSV.
     *
     * @var array<int, string>
     */
    protected const REQUIRED_COLUMNS = ['name'];

    /**
     * Column headers that may be present in the uploaded CSV.
     *
     * @var array<int, string>
     */
    protected const OPTIONAL_COLUMNS = ['description'];

    private const NAME_MAX_LENGTH = 255;

    private const DESCRIPTION_MAX_LENGTH = 2000;

    /**
     * Inject the required services into the importer service.
     */
    public function __construct(
        protected readonly DataPreparationService $dataPreparation,
    ) {}

    /**
     * The private-disk directory this module's uploads are stored under.
     */
    protected function importStoragePath(): string
    {
        return 'imports/campaign-segments';
    }

    /**
     * The Log::ACTION_IMPORT_* constant for this module's batch log entry.
     */
    protected function importAuditAction(): int
    {
        return Log::ACTION_IMPORT_CAMPAIGN_SEGMENT;
    }

    /**
     * Import campaign segments from an uploaded CSV file in one step.
     *
     * @return array{imported: int, skipped: array<int, array{row: int, reason: string}>}
     */
    public function import(UploadedFile $file, int $actorId): array
    {
        $token = $this->storeUpload($file, $actorId);

        try {
            return $this->commit($token, $actorId);
        } catch (RuntimeException $exception) {
            return [
                'imported' => 0,
                'skipped' => [['row' => 0, 'reason' => $exception->getMessage()]],
            ];
        }
    }

    /**
     * Validate a single row, returning a human readable reason or null if valid.
     *
     * The organisation ID should be the importing actor's organisation. If
     * omitted, the current organisation is used.
     *
     * @param  array<string, mixed>  $data
     */
    protected function validateRow(array $data, ?int $organisationId = null): ?string
    {
        foreach (self::REQUIRED_COLUMNS as $column) {
            if (trim((string) ($data[$column] ?? '')) === '') {
                return "Missing value for '{$column}'";
            }
        }

        $name = trim((string) $data['name']);

        if (mb_strlen($name) > self::NAME_MAX_LENGTH) {
            return 'name must be '.self::NAME_MAX_LENGTH.' characters or fewer';
        }

        if (mb_strlen(trim((string) ($data['description'] ?? ''))) > self::DESCRIPTION_MAX_LENGTH) {
            return 'description must be '.self::DESCRIPTION_MAX_LENGTH.' characters or fewer';
        }

        $organisationId ??= $this->currentOrganisationId();

        if ($organisationId === null) {
            return 'No organisation is available for this import';
        }

        $exists = CampaignSegment::query()
            ->where('organisation_id', $organisationId)
            ->where('name', $name)
            ->exists();

        if ($exists) {
            return 'A segment with this name already exists';
        }

        return null;
    }

    /**
     * Persist a single validated row as a new CampaignSegment.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $context
     */
    protected function persistRow(array $data, int $actorId, array $context = []): void
    {
        $actor = User::findOrFail($actorId);
        $organisationId = $this->currentOrganisationId();

        if ($organisationId === null) {
            throw new RuntimeException('No organisation is available for this import.');
        }

        $payload = $this->dataPreparation->prepareForCreation([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        $campaignSegment = new CampaignSegment($payload);
        $campaignSegment->forceFill([
            'organisation_id' => $organisationId,
            'created_by' => $actor->id,
        ])->save();
    }

    /**
     * Resolve the organisation the import runs for.
     */
    private function currentOrganisationId(): ?int
    {
        return Organisation::current()->id ?? Auth::user()?->organisation_id;
    }
}
