<?php

namespace App\Services\Impersonation;

use App\Models\Log;
use App\Models\Organisation;
use App\Models\OrganisationMembership;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class ManagementService
{
    private const SESSION_ACTOR_KEY = 'impersonation.actor_id';

    private const SESSION_STARTED_AT_KEY = 'impersonation.started_at';

    private const SESSION_ORGANISATION_KEY = 'impersonation.organisation_id';

    private const SESSION_PREVIOUS_ORGANISATION_KEY = 'impersonation.previous_organisation_id';

    private const SESSION_CURRENT_ORGANISATION_KEY = 'current_organisation_id';

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
     *
     * Impersonation is scoped to a single organisation both the actor
     * and target actively belong to — it never grants access to every
     * organisation either user is a member of, and this holds even
     * when the actor is a super admin.
     *
     * @throws RuntimeException if already impersonating, if the actor
     *                          targets themselves, or if the actor and target share no active
     *                          organisation.
     */
    public function start(User $actor, User $target): void
    {
        if ($this->isImpersonating()) {
            throw new RuntimeException('Cannot start a new impersonation session while already impersonating.');
        }

        if ($actor->is($target)) {
            throw new RuntimeException('An actor cannot impersonate themselves.');
        }

        $sharedOrganisationId = $this->resolveSharedActiveOrganisationId($actor, $target);

        if ($sharedOrganisationId === null) {
            throw new RuntimeException('The actor and target do not share an active organisation membership.');
        }

        $previousOrganisationId = $this->request->session()->get(self::SESSION_CURRENT_ORGANISATION_KEY);

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
        $this->request->session()->put(
            self::SESSION_ORGANISATION_KEY,
            $sharedOrganisationId
        );
        $this->request->session()->put(
            self::SESSION_PREVIOUS_ORGANISATION_KEY,
            $previousOrganisationId
        );
        $this->request->session()->put(
            self::SESSION_CURRENT_ORGANISATION_KEY,
            $sharedOrganisationId
        );

        $this->auditLogService->record(
            Log::ACTION_START_IMPERSONATION,
            $actor,
            $target,
            [
                'target_id' => $target->id,
                'target_name' => $target->name,
                'organisation_id' => $sharedOrganisationId,
            ],
            $target,
        );
    }

    /**
     * Stop impersonating and restore the original actor's session,
     * including whichever organisation was active beforehand.
     *
     * @throws RuntimeException if no impersonation session is active.
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
        $organisationId = $this->request->session()->get(self::SESSION_ORGANISATION_KEY);
        $previousOrganisationId = $this->request->session()->get(self::SESSION_PREVIOUS_ORGANISATION_KEY);

        $this->request->session()->regenerate();

        Auth::login($actor);

        $this->request->session()->put(
            self::SESSION_CURRENT_ORGANISATION_KEY,
            $previousOrganisationId
        );

        $this->request->session()->forget([
            self::SESSION_ACTOR_KEY,
            self::SESSION_STARTED_AT_KEY,
            self::SESSION_ORGANISATION_KEY,
            self::SESSION_PREVIOUS_ORGANISATION_KEY,
        ]);

        $this->auditLogService->record(
            Log::ACTION_STOP_IMPERSONATION,
            $actor,
            $impersonatedUser,
            [
                'target_id' => $impersonatedUser->id,
                'target_name' => $impersonatedUser->name,
                'duration_seconds' => $durationSeconds,
                'organisation_id' => $organisationId,
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

    /**
     * Resolve the organisation the current impersonation session is
     * scoped to, if one is active.
     */
    public function scopedOrganisation(): ?Organisation
    {
        $organisationId = $this->request->session()->get(self::SESSION_ORGANISATION_KEY);

        return $organisationId ? Organisation::find($organisationId) : null;
    }

    /**
     * Resolve an organisation both the actor and target hold an active
     * membership in, so impersonation can be scoped to it.
     *
     * @return int|null The shared organisation's ID, or null if the actor
     *                  and target share no active organisation.
     */
    private function resolveSharedActiveOrganisationId(User $actor, User $target): ?int
    {
        $targetOrganisationIds = OrganisationMembership::query()
            ->where('user_id', $target->id)
            ->where('status', OrganisationMembership::STATUS_ACTIVE)
            ->pluck('organisation_id');

        if ($targetOrganisationIds->isEmpty()) {
            return null;
        }

        $sharedOrganisationId = OrganisationMembership::query()
            ->where('user_id', $actor->id)
            ->where('status', OrganisationMembership::STATUS_ACTIVE)
            ->whereIn('organisation_id', $targetOrganisationIds)
            ->value('organisation_id');

        return $sharedOrganisationId !== null ? (int) $sharedOrganisationId : null;
    }
}
