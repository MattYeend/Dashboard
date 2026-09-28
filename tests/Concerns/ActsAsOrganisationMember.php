<?php

namespace Tests\Concerns;

use App\Models\Activity;
use App\Models\Address;
use App\Models\Comment;
use App\Models\Company;
use App\Models\Contact;
use App\Models\InteractionLog;
use App\Models\Order;
use App\Models\Organisation;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

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

    /**
     * Create a record of the given scoped model for use in tenancy tests.
     * Polymorphic models need a parent to attach to via forModel(), created
     * fresh in whichever organisation is currently active when this runs;
     * every other model is created directly. Always call this from inside
     * the target organisation's execute() context.
     *
     * @param  array<string, mixed>  $attributes
     */
    protected function createTenantTestRecord(
        string $model,
        array $attributes = []
    ): Model {
        $polymorphic = [
            Contact::class,
            Order::class,
            Address::class,
            Comment::class,
            Activity::class,
            InteractionLog::class,
        ];

        if (in_array($model, $polymorphic, true)) {
            $parent = Company::factory()->create();

            return $model::factory()->forModel($parent)->create($attributes);
        }

        return $model::factory()->create($attributes);
    }
}
