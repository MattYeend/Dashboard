<?php

namespace App\Services\Attachments;

use App\Models\Attachment;

class FormatterService
{
    /**
     * Format a single attachment for use as an Inertia prop.
     *
     * Deliberately excludes disk_path - that's an internal storage
     * detail and must never reach the frontend; download_url is the
     * only way the file is ever referenced client-side.
     *
     * @return array<string, mixed>
     */
    public function format(Attachment $attachment): array
    {
        return [
            'id' => $attachment->id,
            'original_filename' => $attachment->original_filename,
            'mime_type' => $attachment->mime_type,
            'size_bytes' => $attachment->size_bytes,
            'size_human' => $this->humanFileSize($attachment->size_bytes),
            'created_at' => $attachment->created_at,
            'deleted_at' => $attachment->deleted_at,
            'creator' => $attachment->creator ? ['id' => $attachment->creator->id, 'name' => $attachment->creator->name] : null,
            'deleter' => $attachment->deleter ? ['id' => $attachment->deleter->id, 'name' => $attachment->deleter->name] : null,
            'download_url' => route('attachments.download', $attachment->id),
        ];
    }

    /**
     * Convert a raw byte count into a short human-readable size
     * (e.g. "1.2 MB") for display in the attachment list.
     */
    private function humanFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $power = $bytes > 0 ? (int) floor(log($bytes, 1024)) : 0;
        $power = min($power, count($units) - 1);

        return round($bytes / (1024 ** $power), 1).' '.$units[$power];
    }
}
