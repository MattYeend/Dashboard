<?php

namespace App\Services\Organisations;

use App\Models\Log;
use App\Models\Organisation;
use App\Models\OrganisationMembership;
use App\Models\User;
use App\Notifications\OrganisationInvitationNotification;
use App\Services\AuditLogService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\PermissionRegistrar;

class InvitationService
{
    /**
     * Number of days an invitation token remains valid for.
     */
    private const INVITATION_EXPIRY_DAYS = 7;

    /**
     * Inject the required services into the invitation service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
        protected readonly MembershipSeatSyncService $membershipSeatSync,
    ) {}

    /**
     * Invite a user, by email, to join the given organisation.
     *
     * Creates the User record if the email doesn't already exist,
     * with a random unusable password until they accept.
     *
     * @throws ValidationException
     */
    public function invite(
        Organisation $organisation,
        string $email,
        User $invitedBy,
        string $invitedRole
    ): OrganisationMembership {
        $user = User::query()->firstOrCreate(
            ['email' => $email],
            [
                'name' => Str::before($email, '@'),
                'password' => Hash::make(Str::random(40)),
            ],
        );

        $this->guardAgainstExistingActiveMember($organisation, $email);

        $token = Str::random(40);

        $membership = OrganisationMembership::query()->updateOrCreate(
            ['organisation_id' => $organisation->id, 'user_id' => $user->id],
            [
                'status' => OrganisationMembership::STATUS_INVITED,
                'invitation_token' => $token,
                'invited_at' => now(),
                'invited_by' => $invitedBy->id,
                'invited_role' => $invitedRole,
                'created_by' => $invitedBy->id,
            ],
        );

        $user->notify(new OrganisationInvitationNotification($organisation, $token));

        $this->auditLogService->record(
            Log::ACTION_INVITE_MEMBER,
            $invitedBy,
            $organisation,
            ['after' => [
                'organisation_id' => $organisation->id,
                'invited_user_id' => $user->id,
                'invited_role' => $invitedRole,
            ]],
        );

        return $membership;
    }

    /**
     * Classify a batch of emails for the organisation without persisting
     * anything - used to preview a bulk invite before it is committed.
     *
     * @param  Collection<int, string>  $emails
     * @return array{invited: array<int, string>, skipped: array<int, string>, invalid: array<int, string>}
     */
    public function previewBulk(Organisation $organisation, Collection $emails): array
    {
        $invited = [];
        $skipped = [];
        $invalid = [];

        foreach ($emails->unique() as $email) {
            $email = trim($email);

            if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $invalid[] = $email;

                continue;
            }

            if ($this->isAlreadyActiveMember($organisation, $email)) {
                $skipped[] = $email;

                continue;
            }

            $invited[] = $email;
        }

        return [
            'invited' => $invited,
            'skipped' => $skipped,
            'invalid' => $invalid,
        ];
    }

    /**
     * Invite multiple users, by email, to join the given organisation in
     * a single batch. Invalid emails and emails already actively
     * belonging to the organisation are skipped rather than failing the
     * whole batch, and the batch is recorded as a single audit log entry.
     *
     * @param  Collection<int, string>  $emails
     * @return array{invited: array<int, string>, skipped: array<int, string>, invalid: array<int, string>}
     */
    public function inviteBulk(
        Organisation $organisation,
        Collection $emails,
        string $invitedRole,
        User $invitedBy
    ): array {
        $invited = [];
        $skipped = [];
        $invalid = [];

        foreach ($emails->unique() as $email) {
            $email = trim($email);

            if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $invalid[] = $email;

                continue;
            }

            if ($this->isAlreadyActiveMember($organisation, $email)) {
                $skipped[] = $email;

                continue;
            }

            $this->createInvitation($organisation, $email, $invitedBy, $invitedRole);

            $invited[] = $email;
        }

        $this->auditLogService->record(
            Log::ACTION_BULK_INVITE_MEMBERS,
            $invitedBy,
            $organisation,
            ['after' => [
                'organisation_id' => $organisation->id,
                'invited_role' => $invitedRole,
                'invited_count' => count($invited),
                'skipped_count' => count($skipped),
                'invalid_count' => count($invalid),
            ]],
        );

        return [
            'invited' => $invited,
            'skipped' => $skipped,
            'invalid' => $invalid,
        ];
    }

    /**
     * Accept an invitation by token, marking the membership active.
     *
     * @throws ValidationException
     */
    public function accept(string $token, User $user): OrganisationMembership
    {
        $membership = OrganisationMembership::query()
            ->where('invitation_token', $token)
            ->where('user_id', $user->id)
            ->where('status', OrganisationMembership::STATUS_INVITED)
            ->first();

        if ($membership === null) {
            throw ValidationException::withMessages([
                'token' => 'This invitation is invalid or does not belong to your account.',
            ]);
        }

        if ($membership->invited_at !== null
            && $membership->invited_at->lt(now()->subDays(self::INVITATION_EXPIRY_DAYS))
        ) {
            throw ValidationException::withMessages([
                'token' => 'This invitation has expired. Please ask for a new one to be sent.',
            ]);
        }

        $membership->forceFill([
            'status' => OrganisationMembership::STATUS_ACTIVE,
            'joined_at' => now(),
            'invitation_token' => null,
            'updated_by' => $user->id,
        ])->save();

        $this->assignInvitedRole($membership->organisation_id, $user, $membership->invited_role);

        $organisation = Organisation::findOrFail($membership->organisation_id);
        $this->membershipSeatSync->sync($organisation, $user);

        $this->auditLogService->record(
            Log::ACTION_ACCEPT_INVITATION,
            $user,
            $membership,
            ['after' => $this->auditLogService->snapshot($membership)],
        );

        return $membership;
    }

    /**
     * Remove a member from an organisation, revoking their access.
     */
    public function removeMember(
        Organisation $organisation,
        User $member,
        User $actor
    ): void {
        $membership = OrganisationMembership::query()
            ->where('organisation_id', $organisation->id)
            ->where('user_id', $member->id)
            ->firstOrFail();

        $before = $this->auditLogService->snapshot($membership);

        $membership->delete();

        $this->membershipSeatSync->sync($organisation, $actor);

        $this->auditLogService->record(
            Log::ACTION_REMOVE_MEMBER,
            $actor,
            $organisation,
            ['before' => $before],
        );
    }

    /**
     * Create the invitation record and notify the invitee, without
     * recording an audit log entry - callers are responsible for
     * logging, since a bulk invite records one summarising entry rather
     * than one per invitee.
     */
    private function createInvitation(
        Organisation $organisation,
        string $email,
        User $invitedBy,
        string $invitedRole
    ): OrganisationMembership {
        $user = User::query()->firstOrCreate(
            ['email' => $email],
            [
                'name' => Str::before($email, '@'),
                'password' => Hash::make(Str::random(40)),
            ],
        );

        $token = Str::random(40);

        $membership = OrganisationMembership::query()->updateOrCreate(
            ['organisation_id' => $organisation->id, 'user_id' => $user->id],
            [
                'status' => OrganisationMembership::STATUS_INVITED,
                'invitation_token' => $token,
                'invited_at' => now(),
                'invited_by' => $invitedBy->id,
                'invited_role' => $invitedRole,
                'created_by' => $invitedBy->id,
            ],
        );

        $user->notify(new OrganisationInvitationNotification($organisation, $token));

        return $membership;
    }

    /**
     * Determine whether the given email already belongs to an active
     * member of the organisation.
     */
    private function isAlreadyActiveMember(Organisation $organisation, string $email): bool
    {
        $user = User::query()->where('email', $email)->first();

        if ($user === null) {
            return false;
        }

        return OrganisationMembership::query()
            ->where('organisation_id', $organisation->id)
            ->where('user_id', $user->id)
            ->where('status', OrganisationMembership::STATUS_ACTIVE)
            ->exists();
    }

    /**
     * Throw a validation exception if the given email already belongs to
     * an active member of the organisation.
     *
     * @throws ValidationException
     */
    private function guardAgainstExistingActiveMember(Organisation $organisation, string $email): void
    {
        if ($this->isAlreadyActiveMember($organisation, $email)) {
            throw ValidationException::withMessages([
                'email' => 'This user is already a member of this organisation.',
            ]);
        }
    }

    /**
     * Give the invitee the role chosen at invitation time, scoped to the
     * organisation's Spatie permissions team. Falls back to 'User' for
     * any invitation created before roles-at-invite-time was added.
     */
    protected function assignInvitedRole(int $organisationId, User $user, ?string $invitedRole): void
    {
        $registrar = app(PermissionRegistrar::class);
        $previousTeamId = $registrar->getPermissionsTeamId();

        $registrar->setPermissionsTeamId($organisationId);
        $user->unsetRelation('roles')->unsetRelation('permissions');
        $user->assignRole($invitedRole ?? 'User');

        $registrar->setPermissionsTeamId($previousTeamId);
        $user->unsetRelation('roles')->unsetRelation('permissions');
    }
}
