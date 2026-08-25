<?php

namespace App\Enums;

enum InteractionLogType: string
{
    case Call = 'call';
    case Email = 'email';

    /**
     * Get all enum values as an array.
     *
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(fn (self $case) => $case->value, self::cases());
    }

    /**
     * Get a human-readable label for the type.
     */
    public function label(): string
    {
        return match ($this) {
            self::Call => 'Call',
            self::Email => 'Email',
        };
    }
}
