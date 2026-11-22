<?php

declare(strict_types=1);

namespace App\Support\CampaignSegments;

final class CriteriaRules
{
    public const TYPE_BOOLEAN = 'boolean';

    public const TYPE_INTEGER = 'integer';

    /**
     * Maximum number of criteria rows in one segment.
     */
    public const MAX_CRITERIA = 20;

    /**
     * Inclusive bounds for day-based integer values.
     */
    public const MIN_DAYS = 1;

    public const MAX_DAYS = 3650;

    /**
     * Allow-list of criteria fields. Add the real fields here as the
     * contact model grows.
     *
     * @var array<string, array{label: string, type: string, operators: array<int, string>}>
     */
    public const FIELDS = [
        'email_subscribed' => [
            'label' => 'Subscribed to email',
            'type' => self::TYPE_BOOLEAN,
            'operators' => ['equals'],
        ],
        'created_within_days' => [
            'label' => 'Created within (days)',
            'type' => self::TYPE_INTEGER,
            'operators' => ['equals', 'less_than'],
        ],
        'inactive_days' => [
            'label' => 'Inactive for (days)',
            'type' => self::TYPE_INTEGER,
            'operators' => ['equals', 'greater_than', 'less_than'],
        ],
    ];

    /**
     * Allow-list of operators and their labels.
     *
     * @var array<string, string>
     */
    public const OPERATORS = [
        'equals' => 'Equals',
        'greater_than' => 'Greater than',
        'less_than' => 'Less than',
    ];

    /**
     * @return array<int, string>
     */
    public static function fieldNames(): array
    {
        return array_keys(self::FIELDS);
    }

    /**
     * @return array<int, string>
     */
    public static function operatorNames(): array
    {
        return array_keys(self::OPERATORS);
    }

    /**
     * The operators a field accepts, or an empty array for an unknown field.
     *
     * @return array<int, string>
     */
    public static function operatorsFor(string $field): array
    {
        return self::FIELDS[$field]['operators'] ?? [];
    }

    /**
     * The value type of a field, or null for an unknown field.
     */
    public static function typeFor(string $field): ?string
    {
        return self::FIELDS[$field]['type'] ?? null;
    }

    /**
     * Whether a value is acceptable for the given field.
     */
    public static function isValidValue(string $field, mixed $value): bool
    {
        return match (self::typeFor($field)) {
            self::TYPE_BOOLEAN => filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) !== null
                && ! is_array($value),
            self::TYPE_INTEGER => is_scalar($value)
                && filter_var($value, FILTER_VALIDATE_INT, [
                    'options' => ['min_range' => self::MIN_DAYS, 'max_range' => self::MAX_DAYS],
                ]) !== false,
            default => false,
        };
    }

    /**
     * Cast an already validated value to its stored type.
     */
    public static function castValue(string $field, mixed $value): bool|int|null
    {
        return match (self::typeFor($field)) {
            self::TYPE_BOOLEAN => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            self::TYPE_INTEGER => (int) $value,
            default => null,
        };
    }

    /**
     * Field options for the criteria form, including each field's type and
     * the operators it accepts.
     *
     * @return array<int, array{value: string, label: string, type: string, operators: array<int, string>}>
     */
    public static function fieldOptions(): array
    {
        $options = [];

        foreach (self::FIELDS as $value => $definition) {
            $options[] = [
                'value' => $value,
                'label' => $definition['label'],
                'type' => $definition['type'],
                'operators' => $definition['operators'],
            ];
        }

        return $options;
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function operatorOptions(): array
    {
        $options = [];

        foreach (self::OPERATORS as $value => $label) {
            $options[] = ['value' => $value, 'label' => $label];
        }

        return $options;
    }
}
