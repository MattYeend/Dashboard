<?php

namespace App\Services\Organisations;

use App\Models\Organisation;
use App\Models\OrganisationDataExport;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

/**
 * Builds a full data export archive for an Organisation, gathering every
 * record scoped to it across all tenant-scoped models.
 */
class DataExportService
{
    /**
     * Export every record scoped to the given Organisation into a single
     * zip archive of per-model CSV files, and record it as complete.
     */
    public function export(
        Organisation $organisation,
        User $requestedBy
    ): OrganisationDataExport {
        $directory = 'organisation-exports/'.$organisation->id.'/'.now()->format('Ymd-His');
        Storage::disk('local')->makeDirectory($directory);

        foreach (config('organisations.scoped_models') as $modelClass) {
            $this->exportModel($modelClass, $organisation, $directory);
        }

        $zipPath = $directory.'.zip';
        $this->zipDirectory($directory, $zipPath);
        Storage::disk('local')->deleteDirectory($directory);

        return OrganisationDataExport::query()->create([
            'organisation_id' => $organisation->id,
            'requested_by' => $requestedBy->id,
            'disk_path' => $zipPath,
            'completed_at' => now(),
        ]);
    }

    /**
     * Export a single scoped model's records for the organisation into a
     * CSV file inside the given directory.
     *
     * NOTE: writes CSV directly rather than via a shared ExportService,
     * since #343's App\Services\ExportService doesn't exist in this
     * codebase yet. If #343 lands later, replace the arrayToCsv() call
     * below with that service's equivalent and remove arrayToCsv().
     */
    private function exportModel(
        string $modelClass,
        Organisation $organisation,
        string $directory
    ): void {
        $records = $modelClass::query()
            ->withoutGlobalScope('organisation')
            ->where('organisation_id', $organisation->id)
            ->get();

        if ($records->isEmpty()) {
            return;
        }

        $columns = (new $modelClass())->getFillable();
        $csv = $this->arrayToCsv($records->toArray(), $columns);
        $filename = Str::snake(class_basename($modelClass)).'.csv';

        Storage::disk('local')->put($directory.'/'.$filename, $csv);
    }

    /**
     * Convert an array of associative row arrays into CSV content, using
     * the given columns as the header row and the value extraction order.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @param  array<int, string>  $columns
     */
    private function arrayToCsv(array $rows, array $columns): string
    {
        $handle = fopen('php://temp', 'r+');

        fputcsv($handle, $columns);

        foreach ($rows as $row) {
            fputcsv($handle, array_map(
                fn (string $column) => $this->stringifyValue($row[$column] ?? null),
                $columns,
            ));
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv;
    }

    /**
     * Stringify a raw column value for CSV output, flattening arrays and
     * objects to JSON so nothing is silently dropped.
     */
    private function stringifyValue(mixed $value): string
    {
        return match (true) {
            $value === null => '',
            is_bool($value) => $value ? '1' : '0',
            is_array($value) => json_encode($value),
            default => (string) $value,
        };
    }

    /**
     * Compress an export directory on the local disk into a single zip file.
     */
    private function zipDirectory(
        string $directory,
        string $zipPath
    ): void {
        $zip = new ZipArchive();
        $zip->open(Storage::disk('local')->path($zipPath), ZipArchive::CREATE | ZipArchive::OVERWRITE);

        foreach (Storage::disk('local')->files($directory) as $file) {
            $zip->addFile(Storage::disk('local')->path($file), basename($file));
        }

        $zip->close();
    }
}
