<?php

namespace Tests\Concerns;

use App\Models\Organisation;
use App\Models\User;

/**
 * @method static $this withSession(array $data)
 * @method static $this actingAsWithoutOrganisation(User $user)
 */
trait ActsAsOrganisationMember
{
    protected function memberOf(
        Organisation $organisation,
        string $role = 'Super Admin'
    ): User {
        $user = User::factory()->create();

        $organisation->addActiveMember($user->id);

        setPermissionsTeamId($organisation->id);

        $user->assignRole($role);

        return $user;
    }

    protected function actingAsMemberOf(
        Organisation $organisation,
        ?User $user = null
    ): static {
        $user ??= $this->memberOf($organisation);

        $this->withSession([
            'current_organisation_id' => $organisation->id,
        ]);

        return $this->actingAsWithoutOrganisation($user);
    }
}
