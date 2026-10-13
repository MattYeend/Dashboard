<?php
 
declare(strict_types=1);
 
namespace App\Enums;
 
/**
 * Difficulty levels a course can be tagged with.
 */
enum CourseDifficulty: string
{
    case Beginner = 'beginner';
    case Intermediate = 'intermediate';
    case Advanced = 'advanced';
 
    /**
     * Get a human readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Beginner => 'Beginner',
            self::Intermediate => 'Intermediate',
            self::Advanced => 'Advanced',
        };
    }
 
    /**
     * Get every case as a value and label pair for select inputs.
     *
     * @return array<int, array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            static fn (self $case): array => ['value' => $case->value, 'label' => $case->label()],
            self::cases(),
        );
    }
}
