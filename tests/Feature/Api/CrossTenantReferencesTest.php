<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\Industry;
use App\Models\Tag;
use App\Models\TaskStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\Api\Concerns\InteractsWithTenants;
use Tests\TestCase;

class CrossTenantReferencesTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithTenants;

    /**
     * @return array<string, array{0: string}>
     */
    public static function authModes(): array
    {
        return ['bearer' => ['bearer'], 'spa' => ['spa']];
    }

    #[DataProvider('authModes')]
    public function test_task_cannot_use_another_organisations_status(string $mode): void
    {
        $own = $this->makeOrganisation();
        $other = $this->makeOrganisation();
        $user = $this->makeMember($own);
        $status = $other->execute(fn () => TaskStatus::factory()->create());

        $this->authenticateAs($user, $own, $mode)
            ->postJson('/api/tasks', ['title' => 'Task', 'status_id' => $status->id])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('status_id');
    }

    #[DataProvider('authModes')]
    public function test_task_cannot_use_another_organisations_tag(string $mode): void
    {
        $own = $this->makeOrganisation();
        $other = $this->makeOrganisation();
        $user = $this->makeMember($own);
        $tag = $other->execute(fn () => Tag::factory()->create());

        $this->authenticateAs($user, $own, $mode)
            ->postJson('/api/tasks', ['title' => 'Task', 'tag_ids' => [$tag->id]])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('tag_ids.0');
    }

    #[DataProvider('authModes')]
    public function test_task_cannot_be_assigned_to_a_user_outside_the_organisation(string $mode): void
    {
        $own = $this->makeOrganisation();
        $user = $this->makeMember($own);
        $outsider = User::factory()->create();

        $this->authenticateAs($user, $own, $mode)
            ->postJson('/api/tasks', ['title' => 'Task', 'assigned_to' => $outsider->id])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('assigned_to');
    }

    #[DataProvider('authModes')]
    public function test_company_cannot_use_another_organisations_industry(string $mode): void
    {
        $own = $this->makeOrganisation();
        $other = $this->makeOrganisation();
        $user = $this->makeMember($own);
        $industry = $other->execute(fn () => Industry::factory()->create());

        $this->authenticateAs($user, $own, $mode)
            ->postJson('/api/companies', ['name' => 'Acme', 'industry_id' => $industry->id])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('industry_id');
    }

    #[DataProvider('authModes')]
    public function test_contact_cannot_belong_to_a_user_outside_the_organisation(string $mode): void
    {
        $own = $this->makeOrganisation();
        $user = $this->makeMember($own);
        $outsider = User::factory()->create();

        $this->authenticateAs($user, $own, $mode)
            ->postJson('/api/contacts', [
                'contactable_type' => 'user',
                'contactable_id' => $outsider->id,
                'name' => 'Contact',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('contactable_id');
    }

    #[DataProvider('authModes')]
    public function test_company_response_does_not_list_users_from_other_organisations(string $mode): void
    {
        $own = $this->makeOrganisation();
        $other = $this->makeOrganisation();
        $user = $this->makeMember($own);
        $outsider = $this->makeMember($other);
        $company = $own->execute(fn () => Company::factory()->create());

        $response = $this->authenticateAs($user, $own, $mode)
            ->getJson("/api/companies/{$company->id}")
            ->assertOk();

        $ids = collect($response->json('users'))->pluck('id');

        $this->assertTrue($ids->contains($user->id));
        $this->assertFalse($ids->contains($outsider->id));
    }

    #[DataProvider('authModes')]
    public function test_company_slug_must_be_unique_across_organisations(string $mode): void
    {
        $own = $this->makeOrganisation();
        $other = $this->makeOrganisation();
        $user = $this->makeMember($own);
        $other->execute(fn () => Company::factory()->create(['slug' => 'shared-slug']));

        $this->authenticateAs($user, $own, $mode)
            ->postJson('/api/companies', ['name' => 'Acme', 'slug' => 'shared-slug'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('slug');
    }

    #[DataProvider('authModes')]
    public function test_company_registration_number_must_be_unique_across_organisations(string $mode): void
    {
        $own = $this->makeOrganisation();
        $other = $this->makeOrganisation();
        $user = $this->makeMember($own);
        $other->execute(fn () => Company::factory()->create(['registration_number' => '12345678']));

        $this->authenticateAs($user, $own, $mode)
            ->postJson('/api/companies', ['name' => 'Acme', 'registration_number' => '12345678'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('registration_number');
    }
}
