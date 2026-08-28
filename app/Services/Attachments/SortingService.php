<?php

namespace App\Services\Attachments;

use App\Models\Attachment;
use Illuminate\Database\Eloquent\Builder;

class SortingService
{
    /**
     * Apply sorting to query.
     *
     * @param  Builder<Attachment>  $query
     * @return Builder<Attachment>
     */
    public function applySorting(
        Builder $query,
        ?string $sortBy = 'created_at',
        ?string $sortDirection = 'desc'
    ): Builder {
        $sortBy = $sortBy ?? 'created_at';
        $sortDirection = strtolower($sortDirection ?? 'desc') === 'asc' ? 'asc' : 'desc';

        return match ($sortBy) {
            'original_filename' => $query->orderBy('original_filename', $sortDirection),
            'size_bytes' => $query->orderBy('size_bytes', $sortDirection),
            default => $query->orderBy('created_at', $sortDirection),
        };
    }

    /**
     * Get available sort fields.
     *
     * @return array<string, string>
     */
    public function getAvailableSortFields(): array
    {
        return [
            'original_filename' => 'Filename',
            'size_bytes' => 'Size',
            'created_at' => 'Uploaded date',
        ];
    }
}
