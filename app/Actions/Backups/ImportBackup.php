<?php

namespace App\Actions\Backups;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use ZipArchive;

class ImportBackup
{
    /**
     * Store an uploaded zip as a backup on the configured disk.
     *
     * Validates the file is actually a readable zip archive (not just an
     * extension check) before it's persisted anywhere.
     */
    public function handle(UploadedFile $file): string
    {
        $zip = new ZipArchive;

        if ($zip->open($file->getRealPath()) !== true) {
            throw new RuntimeException('The uploaded file is not a valid zip archive.');
        }

        $zip->close();

        $disk = config('backup.backup.destination.disks')[0];
        $appName = Str::slug(config('backup.backup.name'));
        $filename = now()->format('Y-m-d-H-i-s').'-imported-'.Str::random(8).'.zip';

        $file->storeAs($appName, $filename, $disk);

        return $filename;
    }
}
