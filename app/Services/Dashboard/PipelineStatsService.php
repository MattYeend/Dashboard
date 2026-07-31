<?php

namespace App\Services\Dashboard;

use App\Models\Pipeline;

class PipelineStatsService
{
    /**
     * Get total, won and lost pipeline counts.
     *
     * Won/lost is determined by PipelineStatus title ("Won"/"Lost").
     *
     * @return array{total: int, won: int, lost: int}
     */
    public function summary(): array
    {
        $total = Pipeline::query()->count();

        $won = Pipeline::query()
            ->whereHas('status', fn ($query) => $query->where('title', 'Won'))
            ->count();

        $lost = Pipeline::query()
            ->whereHas('status', fn ($query) => $query->where('title', 'Lost'))
            ->count();

        return [
            'total' => $total,
            'won' => $won,
            'lost' => $lost,
        ];
    }
}
