<?php

namespace App\Enums;

enum ActivityType: string
{
    case Note = 'note';
    case StatusChange = 'status_change';
    case TaskCreated = 'task_created';
    case CallLogged = 'call_logged';
    case EmailLogged = 'email_logged';

    /**
     * Get the human-readable label for the activity type.
     */
    public function label(): string
    {
        return match ($this) {
            self::Note => 'Note',
            self::StatusChange => 'Status change',
            self::TaskCreated => 'Task created',
            self::CallLogged => 'Call logged',
            self::EmailLogged => 'Email logged',
        };
    }

    /**
     * Get all enum values as an array of strings.
     *
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(fn (self $type) => $type->value, self::cases());
    }
}
