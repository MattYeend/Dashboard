<?php

namespace App\Services\Logs;

use App\Models\Log;

class FormatterService
{
    /**
     * Format a single activity log with all data.
     *
     * @return array<string, mixed>
     */
    public function format(Log $log): array
    {
        return [
            'id' => $log->id,
            'action_id' => $log->action_id,
            'action_label' => Log::actionLabel($log->action_id),
            'data' => $log->data,
            'logged_in_user_id' => $log->logged_in_user_id,
            'related_to_user_id' => $log->related_to_user_id,
            'logged_in_user' => $log->loggedInUser ? ['id' => $log->loggedInUser->id, 'name' => $log->loggedInUser->name] : null,
            'related_to_user' => $log->relatedToUser ? ['id' => $log->relatedToUser->id, 'name' => $log->relatedToUser->name] : null,
            'created_at' => $log->created_at,
        ];
    }
}
