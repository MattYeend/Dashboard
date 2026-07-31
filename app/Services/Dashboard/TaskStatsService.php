<?php

namespace App\Services\Dashboard;

use App\Models\Task;
use App\Models\User;

class TaskStatsService
{
    /**
     * Get completed and outstanding task counts for the given user.
     *
     * Completion is determined by TaskStatus title: "Done" counts as
     * completed, all statuses except "Done" and "Cancelled" count as
     * outstanding.
     *
     * @return array{completed: int, outstanding: int}
     */
    public function forUser(User $user): array
    {
        $completed = Task::query()
            ->where('assigned_to', $user->id)
            ->whereHas('status', fn ($query) => $query->where('title', 'Done'))
            ->count();

        $outstanding = Task::query()
            ->where('assigned_to', $user->id)
            ->whereHas('status', fn ($query) => $query->whereNotIn('title', ['Done', 'Cancelled']))
            ->count();

        return [
            'completed' => $completed,
            'outstanding' => $outstanding,
        ];
    }
}
