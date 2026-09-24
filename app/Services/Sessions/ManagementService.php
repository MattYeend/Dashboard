<?php

namespace App\Services\Sessions;

use App\Models\Log;
use App\Models\User;
use App\Services\AuditLogService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ManagementService
{
    public function __construct(
        private readonly AuditLogService $auditLogService
    ) {}

    /**
     * @return Collection<int, array{ip_address: mixed, user_agent: string, last_active_at: string, is_current: bool}>
     */
    public function listFor(User $user, string $currentSessionId): Collection
    {
        if (! $this->usesDatabaseDriver()) {
            return collect();
        }

        return DB::table($this->table())
            ->where('user_id', $user->id)
            ->orderByDesc('last_activity')
            ->get(['id', 'ip_address', 'user_agent', 'last_activity'])
            ->map(fn (object $session): array => [
                'ip_address' => $session->ip_address,
                'user_agent' => Str::limit((string) $session->user_agent, 160),
                'last_active_at' => CarbonImmutable::createFromTimestamp($session->last_activity)->toIso8601String(),
                'is_current' => $session->id === $currentSessionId,
            ]);
    }

    public function revokeOthers(User $user, string $currentSessionId): int
    {
        if (! $this->usesDatabaseDriver()) {
            return 0;
        }

        $revoked = DB::table($this->table())
            ->where('user_id', $user->id)
            ->where('id', '!=', $currentSessionId)
            ->delete();

        $this->auditLogService->record(
            Log::ACTION_REVOKE_OTHER_SESSIONS,
            $user,
            $user,
            ['after' => ['revoked_sessions' => $revoked]],
        );

        return $revoked;
    }

    private function usesDatabaseDriver(): bool
    {
        return config('session.driver') === 'database';
    }

    private function table(): string
    {
        return (string) config('session.table', 'sessions');
    }
}
