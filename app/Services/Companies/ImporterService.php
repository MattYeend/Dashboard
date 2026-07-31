<?php

namespace App\Services\Companies;

use App\Models\Company;
use App\Models\Industry;
use App\Models\Log;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImporterService
{
    protected const REQUIRED_COLUMNS = [
        'name',
    ];

    /**
     * Inject the required services into the importer service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
    ) {}

    /**
     * Import companies from an uploaded CSV file.
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

                $data = array_combine($header, $row);

                $error = $this->validateRow($data);

                if ($error !== null) {
                    $skipped[] = ['row' => $rowNumber, 'reason' => $error];

                    continue;
                }

                $company = Company::create([
                    'name' => $data['name'],
                    'slug' => ! empty($data['slug']) ? $data['slug'] : Str::slug($data['name']),
                    'email' => $data['email'] ?? null,
                    'phone' => $data['phone'] ?? null,
                    'website' => $data['website'] ?? null,
                    'registration_number' => $data['registration_number'] ?? null,
                    'vat_number' => $data['vat_number'] ?? null,
                    'description' => $data['description'] ?? null,
                    'industry_id' => ! empty($data['industry_id']) ? (int) $data['industry_id'] : null,
                    'employee_count' => ! empty($data['employee_count']) ? (int) $data['employee_count'] : null,
                    'founded_year' => ! empty($data['founded_year']) ? (int) $data['founded_year'] : null,
                    'created_by' => $actorId,
                ]);

                $this->auditLogService->record(
                    Log::ACTION_IMPORT_COMPANY,
                    $actor,
                    $company,
                    ['after' => $this->auditLogService->snapshot($company)],
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

        if (mb_strlen($data['name']) > 255) {
            return "'name' exceeds 255 characters";
        }

        $slug = ! empty($data['slug']) ? $data['slug'] : Str::slug($data['name']);

        if (Company::withTrashed()->where('slug', $slug)->exists()) {
            return "Slug '{$slug}' already exists";
        }

        if (! empty($data['industry_id'])) {
            if (! ctype_digit((string) $data['industry_id'])) {
                return "'industry_id' must be a positive integer";
            }

            if (! Industry::query()->whereKey((int) $data['industry_id'])->exists()) {
                return "'industry_id' {$data['industry_id']} does not exist";
            }
        }

        if (! empty($data['employee_count']) && ! ctype_digit((string) $data['employee_count'])) {
            return "'employee_count' must be a positive integer";
        }

        if (! empty($data['founded_year'])) {
            if (! ctype_digit((string) $data['founded_year'])) {
                return "'founded_year' must be a valid year";
            }

            $year = (int) $data['founded_year'];

            if ($year < 1500 || $year > (int) now()->format('Y')) {
                return "'founded_year' {$data['founded_year']} is out of range";
            }
        }

        return null;
    }
}
