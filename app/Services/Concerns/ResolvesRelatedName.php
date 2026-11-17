<?php

declare(strict_types=1);

namespace App\Services\Concerns;

use Illuminate\Database\Eloquent\Model;

/**
 * Resolves a display name for a polymorphic relation, falling back to
 * its key when neither a name nor a title is available.
 */
trait ResolvesRelatedName
{
    /**
     * Resolve a display name for the related model, or null when there is none.
     */
    protected function relatedName(?Model $related): ?string
    {
        if ($related === null) {
            return null;
        }

        foreach (['name', 'title'] as $attribute) {
            $value = $related->getAttribute($attribute);

            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        return '#'.$related->getKey();
    }
}