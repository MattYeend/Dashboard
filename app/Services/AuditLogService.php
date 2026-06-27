<?php

namespace App\Services;

use App\Contracts\Auditable;
use App\Models\Log;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AuditLogService
{
    /**
     * Record an audit log entry.
     *
     * @param  array<string, mixed>  $data
     */
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

    /**
     * Capture a snapshot from any model implementing Auditable.
     *
     * @return array<string, mixed>
     */
    public function snapshot(Auditable $model): array
    {
        return $model->auditSnapshot();
    }
}
