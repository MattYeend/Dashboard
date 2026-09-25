<?php

namespace App\Services;

class SensitiveDataMaskerService
{
    public const MASK = '[REDACTED]';

    /**
     * Replace the value of every sensitive key, at any depth.
     */
    public function mask(mixed $data): mixed
    {
        if (! is_array($data)) {
            return $data;
        }

        $fragments = array_map('strtolower', config('audit.sensitive_keys', []));
        $masked = [];

        foreach ($data as $key => $value) {
            $masked[$key] = is_string($key) && $this->isSensitive($key, $fragments)
                ? self::MASK
                : $this->mask($value);
        }

        return $masked;
    }

    /**
     * Determine whether a key contains any sensitive fragment.
     *
     * @param  array<int, string>  $fragments
     */
    private function isSensitive(string $key, array $fragments): bool
    {
        $key = strtolower($key);

        foreach ($fragments as $fragment) {
            if ($fragment !== '' && str_contains($key, $fragment)) {
                return true;
            }
        }

        return false;
    }
}
