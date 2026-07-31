<?php

namespace App\Services\Dashboard;

use App\Models\Deal;

class DealStatsService
{
    /**
     * Get total, won and lost deal counts.
     *
     * Won/lost is determined by DealStatus title ("Won"/"Lost").
     *
     * @return array{total: int, won: int, lost: int}
     */
    public function summary(): array
    {
        $total = Deal::query()->count();

        $won = Deal::query()
            ->whereHas('status', fn ($query) => $query->where('title', 'Won'))
            ->count();

        $lost = Deal::query()
            ->whereHas('status', fn ($query) => $query->where('title', 'Lost'))
            ->count();

        return [
            'total' => $total,
            'won' => $won,
            'lost' => $lost,
        ];
    }
}
