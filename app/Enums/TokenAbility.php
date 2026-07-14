<?php

namespace App\Enums;

enum TokenAbility: string
{
    case TasksRead = 'tasks:read';
    case TasksWrite = 'tasks:write';
    case CompaniesRead = 'companies:read';
    case CompaniesWrite = 'companies:write';
    case OrdersRead = 'orders:read';
    case OrdersWrite = 'orders:write';
    case ContactsRead = 'contacts:read';
    case ContactsWrite = 'contacts:write';

    /**
     * Get a value => label map for the frontend.
     *
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::TasksRead->value => 'View tasks',
            self::TasksWrite->value => 'Create/edit tasks',
            self::CompaniesRead->value => 'View companies',
            self::CompaniesWrite->value => 'Create/edit companies',
            self::OrdersRead->value => 'View orders',
            self::OrdersWrite->value => 'Create/edit orders',
            self::ContactsRead->value => 'View contacts',
            self::ContactsWrite->value => 'Create/edit contacts',
        ];
    }

    /**
     * Get all raw ability values, for validation.
     *
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
