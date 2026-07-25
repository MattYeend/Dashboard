<?php

namespace App\Services;

trait EscapesLikeValues
{
    /**
     * Escape LIKE wildcard characters in a raw search value.
     *
     * Without this, a user searching for e.g. "50%" or "code_a" would have
     * the % and _ characters treated as SQL wildcards rather than literal
     * characters, returning unintended matches.
     */
    private function escapeLikeValue(string $value): string
    {
        return str_replace(
            ['\\', '%', '_'],
            ['\\\\', '\\%', '\\_'],
            $value
        );
    }
}
