<?php

namespace App\Services\Contacts;

use App\Models\Contact;
use App\Models\Log;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class ImporterService
{
    protected const REQUIRED_COLUMNS = [
        'contactable_type',
        'contactable_id',
    ];

    /**
     * Inject the type registry so the import allow-list stays in sync
     * with the single source of truth used elsewhere (e.g. form options).
     */
    public function __construct(
        protected readonly ContactableTypeRegistryService $typeRegistry,
        protected readonly AuditLogService $auditLogService,
    ) {}

    /**
     * Import contacts from an uploaded CSV file.
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

                $contact = Contact::create([
                    'contactable_type' => $this->typeRegistry->modelClassForKey(
                        strtolower(trim($data['contactable_type']))
                    ),
                    'contactable_id' => (int) $data['contactable_id'],
                    'phone' => $data['phone'] ?? null,
                    'email' => $data['email'] ?? null,
                    'created_by' => $actorId,
                ]);

                $this->auditLogService->record(
                    Log::ACTION_IMPORT_CONTACT,
                    $actor,
                    $contact,
                    ['after' => $this->auditLogService->snapshot($contact)],
                );

                $imported++;
            }
        });

        fclose($handle);

        return ['imported' => $imported, 'skipped' => $skipped];
    }

    /**
     * Validate a single row, returning an error string or null if valid.
     */
    private function validateRow(array $data): ?string
    {
        foreach (self::REQUIRED_COLUMNS as $column) {
            if (empty($data[$column])) {
                return "Missing value for '{$column}'";
            }
        }

        $type = strtolower(trim($data['contactable_type']));

        if ($this->typeRegistry->modelClassForKey($type) === null) {
            return "'{$data['contactable_type']}' is not a permitted contactable type";
        }

        if (! ctype_digit((string) $data['contactable_id'])) {
            return 'contactable_id must be a positive integer';
        }

        if (empty($data['phone']) && empty($data['email'])) {
            return 'At least one of phone or email must be provided';
        }

        return null;
    }
}
