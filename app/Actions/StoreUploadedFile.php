<?php

namespace App\Actions;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StoreUploadedFile
{
    /**
     * Store an uploaded file under a randomised name and return the
     * stored path plus the metadata needed to persist an Attachment row.
     *
     * The stored filename is never derived from user input — this is
     * what prevents path traversal and overwrite-by-filename attacks.
     * `original_filename` is kept purely for display.
     *
     * @return array{disk_path: string, original_filename: string, mime_type: string, size_bytes: int}
     */
    public function handle(UploadedFile $file, string $disk): array
    {
        $allowedMimeTypes = config('attachments.allowed_mime_types');
        $detectedMimeType = $file->getMimeType();
        $extension = $allowedMimeTypes[$detectedMimeType] ?? null;

        if ($extension === null) {
            throw new \InvalidArgumentException('File type is not permitted.');
        }

        $storedName = Str::uuid()->toString().'.'.$extension;

        $diskPath = $file->storeAs('', $storedName, $disk);

        return [
            'disk_path' => $diskPath,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $detectedMimeType,
            'size_bytes' => $file->getSize(),
        ];
    }
}
