<?php

declare(strict_types=1);

namespace App\Enums;

enum PaymentMethod: string
{
    case BankTransfer = 'bank_transfer';
    case DirectDebit = 'direct_debit';
    case Card = 'card';
    case Cash = 'cash';
    case Cheque = 'cheque';
    case Other = 'other';

    /**
     * Get the human readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::BankTransfer => 'Bank transfer',
            self::DirectDebit => 'Direct debit',
            self::Card => 'Card',
            self::Cash => 'Cash',
            self::Cheque => 'Cheque',
            self::Other => 'Other',
        };
    }

    /**
     * Get every value, for validation and imports.
     *
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get every case as a value and label pair, for dropdowns.
     *
     * @return array<int, array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $method): array => ['value' => $method->value, 'label' => $method->label()],
            self::cases()
        );
    }
}
