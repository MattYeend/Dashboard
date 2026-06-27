<?php

namespace App\Services;

use App\Models\Log;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AuditLogService
{
    public function record(
        int $actionId,
        ?User $actor,
        ?Model $subject = null,
        array $data = [],
        ?User $relatedUser = null,
    ): Log {
        return Log::create([
            'action_id' => $actionId,
            'logged_in_user_id' => $actor?->id,
            'related_to_user_id' => $relatedUser?->id,
            'data' => array_merge([
                'subject_type' => $subject ? $subject::class : null,
                'subject_id' => $subject?->getKey(),
                'occurred_at' => now()->toISOString(),
            ], $data),
        ]);
    }
}
