<?php

namespace App\Services\Impersonation;

use App\Models\Log;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class ManagementService
{
    private const SESSION_ACTOR_KEY = 'impersonation.actor_id';

    private const SESSION_STARTED_AT_KEY = 'impersonation.started_at';

    /**
     * Inject the required services into the management service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
        protected readonly Request $request,
    ) {}

    /**
     * Start impersonating the target user as the given actor.
     *
     * Rotates the session ID before switching identity to guard
     * against session fixation, then stores the actor's ID so the
     * session can be restored on stop().
     */
    public function start(User $actor, User $target): void
    {
        if ($this->isImpersonating()) {
            throw new RuntimeException('Cannot start a new impersonation session while already impersonating.');
        }

        if ($actor->is($target)) {
            throw new RuntimeException('An actor cannot impersonate themselves.');
        }

        $this->request->session()->regenerate();

        Auth::login($target);

        $this->request->session()->put(
            self::SESSION_ACTOR_KEY,
            $actor->id
        );
        $this->request->session()->put(
            self::SESSION_STARTED_AT_KEY,
            now()->toISOString()
        );

        $this->auditLogService->record(
            Log::ACTION_START_IMPERSONATION,
            $actor,
            $target,
            [
                'target_id' => $target->id,
                'target_name' => $target->name,
            ],
            $target,
        );
    }

    /**
     * Stop impersonating and restore the original actor's session.
     */
    public function stop(User $impersonatedUser): User
    {
        $actorId = $this->request->session()->get(self::SESSION_ACTOR_KEY);

        if (! $actorId) {
            throw new RuntimeException('No impersonation session is currently active.');
        }

        $actor = User::findOrFail($actorId);
        $startedAt = $this->request->session()->get(self::SESSION_STARTED_AT_KEY);
        $durationSeconds = $startedAt ? now()->diffInSeconds($startedAt) : null;

        $this->request->session()->regenerate();

        Auth::login($actor);

        $this->request->session()->forget([
            self::SESSION_ACTOR_KEY,
            self::SESSION_STARTED_AT_KEY,
        ]);

        $this->auditLogService->record(
            Log::ACTION_STOP_IMPERSONATION,
            $actor,
            $impersonatedUser,
            [
                'target_id' => $impersonatedUser->id,
                'target_name' => $impersonatedUser->name,
                'duration_seconds' => $durationSeconds,
            ],
            $impersonatedUser,
        );

        return $actor;
    }

    /**
     * Determine whether an impersonation session is currently active.
     */
    public function isImpersonating(): bool
    {
        return $this->request->session()->has(self::SESSION_ACTOR_KEY);
    }

    /**
     * Resolve the original actor behind the current impersonation
     * session, if one is active.
     */
    public function originalActor(): ?User
    {
        $actorId = $this->request->session()->get(self::SESSION_ACTOR_KEY);

        return $actorId ? User::find($actorId) : null;
    }
}
