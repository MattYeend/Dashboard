<?php

namespace App\Services\Users;

use App\Models\Log;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ImporterService
{
    protected const REQUIRED_COLUMNS = [
        'name',
        'email',
    ];

    protected const ALLOWED_ROLES = [
        'super_admin',
        'admin',
        'user',
    ];

    /**
     * Inject the required services into the importer service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
    ) {}

    /**
     * Import users from an uploaded CSV file.
     *
     * If a row doesn't include a password, a random one is generated;
     * imported users are not sent a welcome email with their password,
     * so an admin should trigger a password reset for them separately.
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

                $role = strtolower(trim($data['role'] ?? 'user'));

                $user = User::create([
                    'name' => $data['name'],
                    'email' => strtolower(trim($data['email'])),
                    'password' => Hash::make($data['password'] ?? Str::random(24)),
                    'created_by' => $actorId,
                ]);

                $user->assignApplicationRole($role);

                $this->auditLogService->record(
                    Log::ACTION_IMPORT_USER,
                    $actor,
                    $user,
                    ['after' => $this->auditLogService->snapshot($user)],
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

        if (! filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return "'{$data['email']}' is not a valid email address";
        }

        if (User::where('email', strtolower(trim($data['email'])))->exists()) {
            return "A user with email '{$data['email']}' already exists";
        }

        if (! empty($data['role']) && ! in_array(strtolower(trim($data['role'])), self::ALLOWED_ROLES, true)) {
            return "'{$data['role']}' is not a permitted role";
        }

        return null;
    }
}
