<?php

namespace App\Services\Dashboard;

use App\Models\Company;
use Illuminate\Support\Carbon;

class CompanyStatsService
{
    /**
     * Get total company count and the count created this calendar month.
     *
     * @return array{total: int, created_this_month: int}
     */
    public function summary(): array
    {
        return [
            'total' => Company::query()->count(),
            'created_this_month' => Company::query()
                ->whereBetween('created_at', [
                    Carbon::now()->startOfMonth(),
                    Carbon::now()->endOfMonth(),
                ])
                ->count(),
        ];
    }
}
