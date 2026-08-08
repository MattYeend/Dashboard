<?php

namespace App\Services\TicketPriorities;

use App\Models\TicketPriority;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ImporterService
{
    /**
     * Import ticket priorities from an uploaded CSV file.
     *
     * @return array{imported: int, updated: int, skipped: int, errors: array<int, string>}
     */
    public function import(UploadedFile $file, User $user): array
    {
        $handle = fopen($file->getRealPath(), 'r');

        if ($handle === false) {
            throw new RuntimeException('Unable to read the uploaded file.');
        }

        $header = fgetcsv($handle);

        if ($header === false) {
            fclose($handle);

            throw new RuntimeException('The uploaded file is empty.');
        }

        $header = array_map(fn (string $column) => trim(strtolower($column)), $header);

        $imported = 0;
        $updated = 0;
        $skipped = 0;
        $errors = [];

        DB::transaction(function () use ($handle, $header, $user, &$imported, &$updated, &$skipped, &$errors) {
            $row = 1;

            while (($data = fgetcsv($handle)) !== false) {
                $row++;

                if (count($data) !== count($header)) {
                    $skipped++;
                    $errors[] = "Row {$row}: column count does not match header.";

                    continue;
                }

                $record = array_combine($header, $data);
                $title = trim((string) ($record['title'] ?? ''));

                if ($title === '') {
                    $skipped++;
                    $errors[] = "Row {$row}: missing required 'title' column.";

                    continue;
                }

                $priority = TicketPriority::withTrashed()->firstOrNew(['title' => $title]);
                $isNew = ! $priority->exists;

                $priority->fill([
                    'level' => isset($record['level']) && $record['level'] !== ''
                        ? max(1, min(4, (int) $record['level']))
                        : 1,
                    'background_colour' => $record['background_colour'] ?: '#6b7280',
                    'text_colour' => $record['text_colour'] ?: '#ffffff',
                    'updated_by' => $user->id,
                ]);

                if ($isNew) {
                    $priority->created_by = $user->id;
                }

                $priority->save();

                $isNew ? $imported++ : $updated++;
            }
        });

        fclose($handle);

        return [
            'imported' => $imported,
            'updated' => $updated,
            'skipped' => $skipped,
            'errors' => $errors,
        ];
    }
}
