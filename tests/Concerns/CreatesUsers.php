<?php

declare(strict_types=1);

namespace Tests\Concerns;

use App\Models\Organisation;
use App\Models\Permission;
use App\Models\User;

trait CreatesUsers
{
    protected function testOrganisation(): Organisation
    {
        $organisation = Organisation::query()->firstOrCreate(
            [
                'slug' => 'test_organisation',
            ],
            [
                'name' => 'Test Organisation',
            ],
        );

        $organisation->makeCurrent();

        setPermissionsTeamId($organisation->id);

        return $organisation;
    }

    /**
     * Create a user who belongs to the test organisation.
     */
    protected function createTenantUser(): User
    {
        $organisation = $this->testOrganisation();

        $user = User::factory()->create();

        $organisation->users()->attach($user);

        setPermissionsTeamId($organisation->id);

        return $user;
    }

    public function adminUser(): User
    {
        $user = $this->createTenantUser();

        $user->assignRole('Admin');

        return $user;
    }

    public function superAdminUser(): User
    {
        $user = $this->createTenantUser();

        $user->assignRole('Super Admin');

        return $user;
    }

    public function normalUser(): User
    {
        $user = $this->createTenantUser();

        $user->assignRole('User');

        return $user;
    }

    public function userWithNoPermissions(): User
    {
        return $this->createTenantUser();
    }

    public function userWithPermissions(array $permissions): User
    {
        $organisation = $this->testOrganisation();

        $user = User::factory()->create();

        $organisation->users()->attach($user);

        setPermissionsTeamId($organisation->id);

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $user->givePermissionTo($permissions);

        return $user;
    }

    /**
     * Add an existing user to another organisation with the given role.
     *
     * Restores the previous permissions team afterwards so the caller's
     * current-organisation context is unchanged.
     */
    protected function addUserToOrganisation(
        User $user,
        Organisation $organisation,
        string $role = 'Admin',
    ): void {
        $previousTeamId = getPermissionsTeamId();

        $organisation->addActiveMember($user->id);

        setPermissionsTeamId($organisation->id);
        $user->assignRole($role);

        setPermissionsTeamId($previousTeamId);

        $user->unsetRelation('roles')->unsetRelation('permissions');
    }

    /**
     * A learner can only view lessons.
     */
    public function learnerUser(): User
    {
        return $this->userWithPermissions(['view any lessons', 'view lessons']);
    }

    /**
     * An editor can manage lessons but is not an admin.
     */
    public function lessonEditorUser(): User
    {
        return $this->userWithPermissions([
            'view any lessons',
            'view lessons',
            'create lessons',
            'edit lessons',
            'delete lessons',
            'restore lessons',
        ]);
    }

    /**
     * Run a callback with another organisation as the current tenant,
     * then restore the previous tenant and permissions team.
     *
     * @template T
     *
     * @param  callable(Organisation): T  $callback
     * @return T
     */
    public function inOrganisation(Organisation $organisation, callable $callback): mixed
    {
        $previous = Organisation::current();
        $previousTeamId = getPermissionsTeamId();

        $organisation->makeCurrent();
        setPermissionsTeamId($organisation->id);

        try {
            return $callback($organisation);
        } finally {
            if ($previous !== null) {
                $previous->makeCurrent();
            } else {
                Organisation::forgetCurrent();
            }

            setPermissionsTeamId($previousTeamId);
        }
    }

    /**
     * Create (or fetch) a second organisation.
     */
    public function otherOrganisation(): Organisation
    {
        return Organisation::query()->firstOrCreate(
            ['slug' => 'organisation-b'],
            ['name' => 'Organisation B'],
        );
    }
}
