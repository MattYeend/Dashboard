<?php

namespace App\Enums;

enum DashboardDateRange: string
{
    case Today = 'today';
    case ThisWeek = 'this_week';
    case ThisMonth = 'this_month';
    case ThisYear = 'this_year';
    case AllTime = 'all_time';

    public function label(): string
    {
        return match ($this) {
            self::Today => 'Today',
            self::ThisWeek => 'This week',
            self::ThisMonth => 'This month',
            self::ThisYear => 'This year',
            self::AllTime => 'All time',
        };
    }
}
