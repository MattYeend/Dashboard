<?php

namespace App\Services\Addresses;

use App\Models\Address;
use App\Models\Log;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\Concerns\ImportsViaPreview;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * Handles CSV import of addresses, including the newer preview/commit
 * workflow (via ImportsViaPreview) alongside the original one-shot
 * import() method.
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
        'addressable_type',
        'addressable_id',
        'address_line_one',
        'city',
        'country',
    ];

    /**
     * Inject the type registry so the import allow-list stays in sync
     * with the single source of truth used elsewhere (e.g. form options).
     */
    public function __construct(
        protected readonly AddressableTypeRegistryService $typeRegistry,
        protected readonly AuditLogService $auditLogService,
    ) {}

    /**
     * The private-disk directory this module's uploads are stored under.
     */
    protected function importStoragePath(): string
    {
        return 'imports/addresses';
    }

    /**
     * The Log::ACTION_IMPORT_* constant for this module's batch log entry.
     */
    protected function importAuditAction(): int
    {
        return Log::ACTION_IMPORT_ADDRESS;
    }

    /**
     * Persist a single validated row as a new Address.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $context
     */
    protected function persistRow(array $data, int $actorId, array $context = []): void
    {
        Address::create([
            'addressable_type' => $this->typeRegistry->modelClassForKey(
                strtolower(trim($data['addressable_type']))
            ),
            'addressable_id' => (int) $data['addressable_id'],
            'address_line_one' => $data['address_line_one'],
            'address_line_two' => $data['address_line_two'] ?? null,
            'town' => $data['town'] ?? null,
            'city' => $data['city'],
            'county' => $data['county'] ?? null,
            'postcode' => $data['postcode'] ?? null,
            'country' => $data['country'],
            'is_primary' => filter_var($data['is_primary'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'created_by' => $actorId,
        ]);
    }

    /**
     * Import addresses from an uploaded CSV file.
     *
     * @return array{imported: int, skipped: array<int, array{row: int, reason: string}>}
     */
    public function import(
        UploadedFile $file,
        int $actorId
    ): array {
        $handle = fopen($file->getRealPath(), 'r');

        $header = fgetcsv($handle);
        $header = array_map(fn (string $column) => strtolower(trim($column)), $header ?: []);

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

                $address = Address::create([
                    'addressable_type' => $this->typeRegistry->modelClassForKey(
                        strtolower(trim($data['addressable_type']))
                    ),
                    'addressable_id' => (int) $data['addressable_id'],
                    'address_line_one' => $data['address_line_one'],
                    'address_line_two' => $data['address_line_two'] ?? null,
                    'town' => $data['town'] ?? null,
                    'city' => $data['city'],
                    'county' => $data['county'] ?? null,
                    'postcode' => $data['postcode'] ?? null,
                    'country' => $data['country'],
                    'is_primary' => filter_var($data['is_primary'] ?? false, FILTER_VALIDATE_BOOLEAN),
                    'created_by' => $actorId,
                ]);

                $this->auditLogService->record(
                    Log::ACTION_IMPORT_ADDRESS,
                    $actor,
                    $address,
                    ['after' => $this->auditLogService->snapshot($address)],
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
        foreach (self::REQUIRED_COLUMNS as $column) {
            if (empty($data[$column])) {
                return "Missing value for '{$column}'";
            }
        }

        $type = strtolower(trim($data['addressable_type']));

        if ($this->typeRegistry->modelClassForKey($type) === null) {
            return "'{$data['addressable_type']}' is not a permitted addressable type";
        }

        if (! ctype_digit((string) $data['addressable_id'])) {
            return 'addressable_id must be a positive integer';
        }

        return null;
    }
}
