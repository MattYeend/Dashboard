<?php

use App\Models\Company;
use App\Models\Industry;
use App\Models\Log;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;
use Tests\Concerns\CreatesUsers;

uses(
    LazilyRefreshDatabase::class,
    CreatesUsers::class,
);

beforeEach(function () {
    setPermissionsTeamId(1);

    Role::firstOrCreate(['name' => 'Admin']);
    Role::firstOrCreate(['name' => 'Super Admin']);
    Role::firstOrCreate(['name' => 'User']);
});

test('example', function () {
    $response = $this->get('/');
    $response->assertStatus(200);
});

describe('index', function () {
    test('authenticated user with permission can list companies', function () {
        $superAdmin = $this->superAdminUser();

        Company::factory()->count(3)->create();

        $this->actingAs($superAdmin)
            ->get('/companies')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Companies/Index')
                ->has('companies')
            );
    });

    test('unauthenticated user cannot list companies', function () {
        $this->get('/companies')
            ->assertRedirect('/login');
    });

    test('user without permission cannot list companies', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->get('/companies')
            ->assertStatus(403);
    });
});

describe('create', function () {
    test('authenticated user with permission can view create form', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/companies/create')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Companies/Create')
            );
    });

    test('unauthenticated user cannot view create form', function () {
        $this->get('/companies/create')
            ->assertRedirect('/login');
    });

    test('user without permission cannot view create form', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->get('/companies/create')
            ->assertStatus(403);
    });
});

describe('store', function () {
    test('authenticated user with permission can create a company', function () {
        $superAdmin = $this->superAdminUser();
        $industry = Industry::factory()->create();

        $payload = [
            'name' => 'Acme Engineering Ltd',
            'slug' => 'acme-engineering-ltd',
            'email' => 'contact@acmeengineering.co.uk',
            'phone' => '01162345678',
            'website' => 'https://www.acmeengineering.co.uk',
            'registration_number' => '01234567',
            'vat_number' => 'GB123456789',
            'description' => 'Precision engineering and manufacturing.',
            'industry_id' => $industry->id,
            'employee_count' => 42,
            'founded_year' => 1998,
        ];

        $this->actingAs($superAdmin)
            ->postJson('/companies', $payload)
            ->assertStatus(201)
            ->assertJsonFragment(['name' => 'Acme Engineering Ltd']);

        $this->assertDatabaseHas('companies', [
            'name' => 'Acme Engineering Ltd',
            'slug' => 'acme-engineering-ltd',
            'industry_id' => $industry->id,
        ]);
    });

    test('user without permission cannot create a company', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->postJson('/companies', [
                'name' => 'Blocked Company Ltd',
            ])
            ->assertStatus(403);
    });

    test('store fails validation when name is missing', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/companies', [
                'slug' => 'no-name-ltd',
                'description' => 'No name here.',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    });

    test('store fails validation when slug already exists', function () {
        $superAdmin = $this->superAdminUser();

        Company::factory()->create(['slug' => 'duplicate-slug-ltd']);

        $this->actingAs($superAdmin)
            ->postJson('/companies', [
                'name' => 'Duplicate Slug Ltd',
                'slug' => 'duplicate-slug-ltd',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['slug']);
    });

    test('store fails validation when email is not a valid format', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/companies', [
                'name' => 'Invalid Email Ltd',
                'email' => 'not-an-email',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    });

    test('store fails validation when industry_id does not exist', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/companies', [
                'name' => 'Invalid Industry Ltd',
                'industry_id' => 99999,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['industry_id']);
    });

    test('store fails validation when employee_count is not an integer', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/companies', [
                'name' => 'Invalid Employee Count Ltd',
                'employee_count' => 'lots',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['employee_count']);
    });

    test('store fails validation when founded_year is not a valid year', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/companies', [
                'name' => 'Invalid Founded Year Ltd',
                'founded_year' => 'not-a-year',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['founded_year']);
    });

    test('store succeeds with only required fields', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/companies', [
                'name' => 'Minimal Company Ltd',
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('companies', [
            'name' => 'Minimal Company Ltd',
            'slug' => 'minimal-company-ltd',
            'email' => null,
            'industry_id' => null,
        ]);
    });

    test('store succeeds with meta data', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/companies', [
                'name' => 'Company With Meta Ltd',
                'meta' => ['tier' => 'enterprise', 'tags' => ['manufacturing']],
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('companies', ['name' => 'Company With Meta Ltd']);
    });
});

describe('show', function () {
    test('authenticated user with permission can view a company', function () {
        $superAdmin = $this->superAdminUser();

        $company = Company::factory()->create();

        $this->actingAs($superAdmin)
            ->get("/companies/{$company->id}")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Companies/Show')
                ->has('company')
            );
    });

    test('unauthenticated user cannot view a company', function () {
        $company = Company::factory()->create();

        $this->get("/companies/{$company->id}")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view a company', function () {
        $user = $this->normalUser();

        $company = Company::factory()->create();

        $this->actingAs($user)
            ->get("/companies/{$company->id}")
            ->assertStatus(403);
    });

    test('show returns 404 for a non-existent company', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/companies/99999')
            ->assertStatus(404);
    });
});

describe('edit', function () {
    test('authenticated user with permission can view edit form', function () {
        $superAdmin = $this->superAdminUser();

        $company = Company::factory()->create();

        $this->actingAs($superAdmin)
            ->get("/companies/{$company->id}/edit")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Companies/Edit')
                ->has('company')
            );
    });

    test('unauthenticated user cannot view edit form', function () {
        $company = Company::factory()->create();

        $this->get("/companies/{$company->id}/edit")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view edit form', function () {
        $user = $this->normalUser();

        $company = Company::factory()->create();

        $this->actingAs($user)
            ->get("/companies/{$company->id}/edit")
            ->assertStatus(403);
    });
});

describe('update', function () {
    test('authenticated user with permission can update a company', function () {
        $superAdmin = $this->superAdminUser();

        $company = Company::factory()->create(['name' => 'Old Name Ltd']);

        $this->actingAs($superAdmin)
            ->putJson("/companies/{$company->id}", ['name' => 'New Name Ltd'])
            ->assertStatus(200)
            ->assertJsonFragment(['name' => 'New Name Ltd']);

        $this->assertDatabaseHas('companies', [
            'id' => $company->id,
            'name' => 'New Name Ltd',
        ]);
    });

    test('patch verb also updates a company', function () {
        $superAdmin = $this->superAdminUser();

        $company = Company::factory()->create(['description' => 'Old description']);

        $this->actingAs($superAdmin)
            ->patchJson("/companies/{$company->id}", ['description' => 'New description'])
            ->assertStatus(200)
            ->assertJsonFragment(['description' => 'New description']);
    });

    test('user without permission cannot update a company', function () {
        $user = $this->normalUser();

        $company = Company::factory()->create();

        $this->actingAs($user)
            ->putJson("/companies/{$company->id}", ['name' => 'New Name Ltd'])
            ->assertStatus(403);
    });

    test('update fails validation when slug already exists on another company', function () {
        $superAdmin = $this->superAdminUser();

        Company::factory()->create(['slug' => 'taken-slug-ltd']);
        $company = Company::factory()->create(['slug' => 'free-slug-ltd']);

        $this->actingAs($superAdmin)
            ->putJson("/companies/{$company->id}", ['slug' => 'taken-slug-ltd'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['slug']);
    });

    test('update allows a company to keep its own slug', function () {
        $superAdmin = $this->superAdminUser();

        $company = Company::factory()->create(['slug' => 'keep-slug-ltd']);

        $this->actingAs($superAdmin)
            ->putJson("/companies/{$company->id}", [
                'slug' => 'keep-slug-ltd',
                'name' => 'Renamed Company Ltd',
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('companies', [
            'id' => $company->id,
            'slug' => 'keep-slug-ltd',
            'name' => 'Renamed Company Ltd',
        ]);
    });

    test('update fails validation when industry_id does not exist', function () {
        $superAdmin = $this->superAdminUser();

        $company = Company::factory()->create();

        $this->actingAs($superAdmin)
            ->putJson("/companies/{$company->id}", ['industry_id' => 99999])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['industry_id']);
    });

    test('nullable fields can be cleared by passing null on update', function () {
        $superAdmin = $this->superAdminUser();
        $industry = Industry::factory()->create();

        $company = Company::factory()->create([
            'email' => 'old@example.co.uk',
            'phone' => '01234567890',
            'website' => 'https://old.example.co.uk',
            'registration_number' => '01234567',
            'vat_number' => 'GB123456789',
            'description' => 'Some description.',
            'industry_id' => $industry->id,
            'employee_count' => 10,
            'founded_year' => 2010,
        ]);

        $this->actingAs($superAdmin)
            ->putJson("/companies/{$company->id}", [
                'email' => null,
                'phone' => null,
                'website' => null,
                'registration_number' => null,
                'vat_number' => null,
                'description' => null,
                'industry_id' => null,
                'employee_count' => null,
                'founded_year' => null,
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('companies', [
            'id' => $company->id,
            'email' => null,
            'phone' => null,
            'website' => null,
            'registration_number' => null,
            'vat_number' => null,
            'description' => null,
            'industry_id' => null,
            'employee_count' => null,
            'founded_year' => null,
        ]);
    });

    test('omitted fields are not cleared on update', function () {
        $superAdmin = $this->superAdminUser();

        $company = Company::factory()->create([
            'email' => 'original@example.co.uk',
            'description' => 'Original description.',
        ]);

        $this->actingAs($superAdmin)
            ->putJson("/companies/{$company->id}", [
                'name' => 'Updated Name Ltd',
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('companies', [
            'id' => $company->id,
            'name' => 'Updated Name Ltd',
            'email' => 'original@example.co.uk',
            'description' => 'Original description.',
        ]);
    });

    test('patch verb can clear nullable fields', function () {
        $superAdmin = $this->superAdminUser();

        $company = Company::factory()->create([
            'description' => 'Some description.',
        ]);

        $this->actingAs($superAdmin)
            ->patchJson("/companies/{$company->id}", [
                'description' => null,
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('companies', [
            'id' => $company->id,
            'description' => null,
        ]);
    });

    test('industry_id can be updated to associate a company with a different industry', function () {
        $superAdmin = $this->superAdminUser();
        $newIndustry = Industry::factory()->create();

        $company = Company::factory()->create();

        $this->actingAs($superAdmin)
            ->putJson("/companies/{$company->id}", [
                'industry_id' => $newIndustry->id,
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('companies', [
            'id' => $company->id,
            'industry_id' => $newIndustry->id,
        ]);
    });

    test('logs company updates with actor id', function () {
        $actor = $this->adminUser();

        $company = Company::factory()->create(['name' => 'Old Name Ltd']);

        $this->actingAs($actor)
            ->putJson("/companies/{$company->id}", ['name' => 'New Name Ltd'])
            ->assertOk();

        $log = Log::query()
            ->where('action_id', Log::ACTION_UPDATE_COMPANY)
            ->where('logged_in_user_id', $actor->id)
            ->first();

        expect($log)->not->toBeNull()
            ->and($log->data)->toHaveKeys(['before', 'after']);
    });
});

describe('destroy', function () {
    test('authenticated user with permission can soft delete a company', function () {
        $superAdmin = $this->superAdminUser();

        $company = Company::factory()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/companies/{$company->id}")
            ->assertStatus(204);

        $this->assertSoftDeleted('companies', ['id' => $company->id]);
    });

    test('user without permission cannot soft delete a company', function () {
        $user = $this->normalUser();

        $company = Company::factory()->create();

        $this->actingAs($user)
            ->deleteJson("/companies/{$company->id}")
            ->assertStatus(403);
    });

    test('destroy returns 404 for a non-existent company', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->deleteJson('/companies/99999')
            ->assertStatus(404);
    });
});

describe('restore', function () {
    test('authenticated user with permission can restore a soft-deleted company', function () {
        $superAdmin = $this->superAdminUser();

        $company = Company::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->postJson("/companies/{$company->id}/restore")
            ->assertStatus(204);

        $this->assertDatabaseHas('companies', [
            'id' => $company->id,
            'deleted_at' => null,
        ]);
    });

    test('user without permission cannot restore a company', function () {
        $user = $this->normalUser();

        $company = Company::factory()->deleted()->create();

        $this->actingAs($user)
            ->postJson("/companies/{$company->id}/restore")
            ->assertStatus(403);
    });

    test('restore returns 404 for a company that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();

        $company = Company::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson("/companies/{$company->id}/restore")
            ->assertStatus(404);
    });
});

describe('force delete', function () {
    test('authenticated user with permission can force delete a company', function () {
        $superAdmin = $this->superAdminUser();

        $company = Company::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/companies/{$company->id}/force")
            ->assertStatus(204);

        $this->assertDatabaseMissing('companies', ['id' => $company->id]);
    });

    test('user without permission cannot force delete a company', function () {
        $user = $this->normalUser();

        $company = Company::factory()->deleted()->create();

        $this->actingAs($user)
            ->deleteJson("/companies/{$company->id}/force")
            ->assertStatus(403);
    });

    test('force delete returns 404 for a company that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();

        $company = Company::factory()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/companies/{$company->id}/force")
            ->assertStatus(404);
    });
});

describe('bulk delete', function () {
    test('authenticated user with permission can bulk soft delete companies', function () {
        $superAdmin = $this->superAdminUser();

        $companies = Company::factory()->count(3)->create();
        $ids = $companies->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson('/companies/bulk/delete', ['ids' => $ids])
            ->assertStatus(204);

        foreach ($ids as $id) {
            $this->assertSoftDeleted('companies', ['id' => $id]);
        }
    });

    test('bulk delete fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/companies/bulk/delete', ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk delete fails validation with non-existent ids', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/companies/bulk/delete', ['ids' => [99999]])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids.0']);
    });

    test('user without permission cannot bulk delete companies', function () {
        $user = $this->normalUser();

        $companies = Company::factory()->count(2)->create();

        $this->actingAs($user)
            ->postJson('/companies/bulk/delete', [
                'ids' => $companies->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('bulk restore', function () {
    test('authenticated user with permission can bulk restore companies', function () {
        $superAdmin = $this->superAdminUser();

        $companies = Company::factory()->count(3)->deleted()->create();
        $ids = $companies->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson('/companies/bulk/restore', ['ids' => $ids])
            ->assertStatus(204);

        foreach ($ids as $id) {
            $this->assertDatabaseHas('companies', [
                'id' => $id,
                'deleted_at' => null,
            ]);
        }
    });

    test('bulk restore fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/companies/bulk/restore', ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk restore fails validation with non-existent ids', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/companies/bulk/restore', ['ids' => [99999]])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids.0']);
    });

    test('user without permission cannot bulk restore companies', function () {
        $user = $this->normalUser();

        $companies = Company::factory()->count(2)->deleted()->create();

        $this->actingAs($user)
            ->postJson('/companies/bulk/restore', [
                'ids' => $companies->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('soft delete scoping', function () {
    test('index does not return soft-deleted companies', function () {
        $superAdmin = $this->superAdminUser();

        Company::factory()->count(2)->create();
        $trashed = Company::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->get('/companies')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Companies/Index')
                ->has('companies')
            );

        $this->assertSoftDeleted('companies', ['id' => $trashed->id]);
    });

    test('show returns 404 for a soft-deleted company', function () {
        $superAdmin = $this->superAdminUser();

        $company = Company::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->get("/companies/{$company->id}")
            ->assertStatus(404);
    });
});
