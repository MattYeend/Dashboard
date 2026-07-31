<?php

namespace App\Services\Addresses;

use App\Models\Address;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class ImporterService
{
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
    ) {}

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

        DB::transaction(function () use ($handle, $header, $actorId, &$imported, &$skipped, &$rowNumber) {
            while (($row = fgetcsv($handle)) !== false) {
                $rowNumber++;

                $data = array_combine($header, $row);

                $error = $this->validateRow($data);

                if ($error !== null) {
                    $skipped[] = ['row' => $rowNumber, 'reason' => $error];

                    continue;
                }

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
