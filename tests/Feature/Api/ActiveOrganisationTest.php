<?php

namespace Tests\Feature\Api;

use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Api\Concerns\InteractsWithTenants;
use Tests\TestCase;

class ActiveOrganisationTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithTenants;

    public function test_spa_request_uses_the_organisation_stored_on_the_session(): void
    {
        $first = $this->makeOrganisation();
        $second = $this->makeOrganisation();
        $user = $this->makeMember($first);
        $this->joinOrganisation($user, $second);

        $first->execute(fn () => Task::factory()->count(1)->create());
        $second->execute(fn () => Task::factory()->count(4)->create());

        $this->authenticateAs($user, $second, 'spa')
            ->getJson('/api/tasks')
            ->assertOk()
            ->assertJsonPath('tasks.meta.total', 4);
    }

    public function test_session_organisation_without_membership_is_ignored(): void
    {
        $own = $this->makeOrganisation();
        $foreign = $this->makeOrganisation();
        $user = $this->makeMember($own);

        $own->execute(fn () => Task::factory()->count(2)->create());
        $foreign->execute(fn () => Task::factory()->count(5)->create());

        $this->authenticateAs($user, $foreign, 'spa')
            ->getJson('/api/tasks')
            ->assertOk()
            ->assertJsonPath('tasks.meta.total', 2);
    }
}
