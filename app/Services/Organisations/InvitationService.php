<?php

namespace App\Services\Organisations;

use App\Models\Log;
use App\Models\Organisation;
use App\Models\OrganisationMembership;
use App\Models\User;
use App\Notifications\OrganisationInvitationNotification;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\PermissionRegistrar;

class InvitationService
{
    /**
     * Inject the required services into the invitation service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
    ) {}

    /**
     * Invite a user, by email, to join the given organisation.
     *
     * Creates the User record if the email doesn't already exist,
     * with a random unusable password until they accept.
     */
    public function invite(
        Organisation $organisation,
        string $email,
        User $invitedBy
    ): OrganisationMembership {
        $user = User::query()->firstOrCreate(
            ['email' => $email],
            [
                'name' => Str::before($email, '@'),
                'password' => Hash::make(Str::random(40)),
            ],
        );

        $existing = OrganisationMembership::query()
            ->where('organisation_id', $organisation->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing !== null && $existing->status === OrganisationMembership::STATUS_ACTIVE) {
            throw ValidationException::withMessages([
                'email' => 'This user is already a member of this organisation.',
            ]);
        }

        $token = Str::random(40);

        $membership = OrganisationMembership::query()->updateOrCreate(
            ['organisation_id' => $organisation->id, 'user_id' => $user->id],
            [
                'status' => OrganisationMembership::STATUS_INVITED,
                'invitation_token' => $token,
                'invited_at' => now(),
                'invited_by' => $invitedBy->id,
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
            ]],
        );

        return $membership;
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
            ->first();

        if ($membership === null) {
            throw ValidationException::withMessages([
                'token' => 'This invitation is invalid or does not belong to your account.',
            ]);
        }

        $membership->forceFill([
            'status' => OrganisationMembership::STATUS_ACTIVE,
            'joined_at' => now(),
            'invitation_token' => null,
            'updated_by' => $user->id,
        ])->save();

        $this->assignDefaultRole($membership->organisation_id, $user);

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

        $this->auditLogService->record(
            Log::ACTION_REMOVE_MEMBER,
            $actor,
            $organisation,
            ['before' => $before],
        );
    }

    /**
     * Give a newly accepted member the default 'User' role, scoped to
     * the organisation's Spatie permissions team.
     */
    protected function assignDefaultRole(int $organisationId, User $user): void
    {
        $registrar = app(PermissionRegistrar::class);
        $previousTeamId = $registrar->getPermissionsTeamId();

        $registrar->setPermissionsTeamId($organisationId);
        $user->unsetRelation('roles')->unsetRelation('permissions');
        $user->assignRole('User');

        $registrar->setPermissionsTeamId($previousTeamId);
        $user->unsetRelation('roles')->unsetRelation('permissions');
    }
}
