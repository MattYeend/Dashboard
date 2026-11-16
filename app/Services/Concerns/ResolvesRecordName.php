<?php

declare(strict_types=1);

namespace App\Services\Concerns;

use Illuminate\Database\Eloquent\Model;

trait ResolvesRecordName
{
    /**
     * Resolve a human readable name for a polymorphic parent record.
     *
     * Falls back from "name" to "title" and finally to "#<key>". Returns
     * null when the parent no longer exists (for example, hard deleted).
     */
    protected function resolveRecordName(?Model $record): ?string
    {
        if ($record === null) {
            return null;
        }

        $name = $record->getAttribute('name') ?? $record->getAttribute('title');

        return is_string($name) && $name !== ''
            ? $name
            : '#'.$record->getKey();
    }
}