<?php

use App\Models\Company;
use App\Models\Deal;
use App\Models\DealStatus;
use App\Models\Invoice;
use App\Models\Log;
use App\Models\Pipeline;
use App\Models\PipelineStage;
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
    test('authenticated user with permission can list deals', function () {
        $superAdmin = $this->superAdminUser();

        Deal::factory()->count(3)->create();

        $this->actingAs($superAdmin)
            ->get('/deals')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Deals/Index')
                ->has('deals')
            );
    });

    test('unauthenticated user cannot list deals', function () {
        $this->get('/deals')
            ->assertRedirect('/login');
    });

    test('user without permission cannot list deals', function () {
        $user = $this->userWithNoPermissions();

        $this->actingAs($user)
            ->get('/deals')
            ->assertStatus(403);
    });
});

describe('create', function () {
    test('authenticated user with permission can view create form', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/deals/create')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Deals/Create')
                ->has('pipelines')
                ->has('pipeline_stages')
                ->has('deal_statuses')
                ->has('companies')
                ->has('invoices')
            );
    });

    test('unauthenticated user cannot view create form', function () {
        $this->get('/deals/create')
            ->assertRedirect('/login');
    });

    test('user without permission cannot view create form', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->get('/deals/create')
            ->assertStatus(403);
    });
});

describe('store', function () {
    test('authenticated user with permission can create a deal', function () {
        $superAdmin = $this->superAdminUser();
        $pipeline = Pipeline::factory()->create();
        $stage = PipelineStage::factory()->create(['pipeline_id' => $pipeline->id]);
        $status = DealStatus::factory()->create();
        $company = Company::factory()->create();
        $invoice = Invoice::factory()->create();

        $payload = [
            'title' => 'Acme Engineering Renewal',
            'description' => 'Annual contract renewal for precision components.',
            'pipeline_id' => $pipeline->id,
            'stage_id' => $stage->id,
            'status_id' => $status->id,
            'company_id' => $company->id,
            'invoice_id' => $invoice->id,
            'value' => 25000,
            'currency' => 'GBP',
            'probability' => 60,
            'expected_close_date' => '2026-09-01',
            'closed_at' => null,
        ];

        $this->actingAs($superAdmin)
            ->postJson('/deals', $payload)
            ->assertStatus(201)
            ->assertJsonFragment(['title' => 'Acme Engineering Renewal']);

        $this->assertDatabaseHas('deals', [
            'title' => 'Acme Engineering Renewal',
            'pipeline_id' => $pipeline->id,
            'company_id' => $company->id,
        ]);
    });

    test('user without permission cannot create a deal', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->postJson('/deals', [
                'title' => 'Blocked Deal',
            ])
            ->assertStatus(403);
    });

    test('store fails validation when title is missing', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/deals', [
                'description' => 'No title here.',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    });

    test('store fails validation when pipeline_id does not exist', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/deals', [
                'title' => 'Invalid Pipeline Deal',
                'pipeline_id' => 99999,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['pipeline_id']);
    });

    test('store fails validation when stage_id does not exist', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/deals', [
                'title' => 'Invalid Stage Deal',
                'stage_id' => 99999,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['stage_id']);
    });

    test('store fails validation when status_id does not exist', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/deals', [
                'title' => 'Invalid Status Deal',
                'status_id' => 99999,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['status_id']);
    });

    test('store fails validation when company_id does not exist', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/deals', [
                'title' => 'Invalid Company Deal',
                'company_id' => 99999,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['company_id']);
    });

    test('store fails validation when invoice_id does not exist', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/deals', [
                'title' => 'Invalid Invoice Deal',
                'invoice_id' => 99999,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['invoice_id']);
    });

    test('store fails validation when value is negative', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/deals', [
                'title' => 'Negative Value Deal',
                'value' => -500,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['value']);
    });

    test('store fails validation when probability is out of range', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/deals', [
                'title' => 'Invalid Probability Deal',
                'probability' => 150,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['probability']);
    });

    test('store succeeds with only required fields', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/deals', [
                'title' => 'Minimal Deal',
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('deals', [
            'title' => 'Minimal Deal',
            'pipeline_id' => null,
            'company_id' => null,
        ]);
    });

    test('store succeeds with meta data', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/deals', [
                'title' => 'Deal with meta',
                'meta' => ['source' => 'referral', 'tags' => ['priority']],
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('deals', ['title' => 'Deal with meta']);
    });
});

describe('show', function () {
    test('authenticated user with permission can view a deal', function () {
        $superAdmin = $this->superAdminUser();

        $deal = Deal::factory()->create();

        $this->actingAs($superAdmin)
            ->get("/deals/{$deal->id}")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Deals/Show')
                ->has('deal')
            );
    });

    test('unauthenticated user cannot view a deal', function () {
        $deal = Deal::factory()->create();

        $this->get("/deals/{$deal->id}")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view a deal', function () {
        $user = $this->userWithNoPermissions();

        $deal = Deal::factory()->create();

        $this->actingAs($user)
            ->get("/deals/{$deal->id}")
            ->assertStatus(403);
    });

    test('show returns 404 for a non-existent deal', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/deals/99999')
            ->assertStatus(404);
    });
});

describe('edit', function () {
    test('authenticated user with permission can view edit form', function () {
        $superAdmin = $this->superAdminUser();

        $deal = Deal::factory()->create();

        $this->actingAs($superAdmin)
            ->get("/deals/{$deal->id}/edit")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Deals/Edit')
                ->has('deal')
                ->has('pipelines')
                ->has('pipeline_stages')
                ->has('deal_statuses')
                ->has('companies')
                ->has('invoices')
            );
    });

    test('unauthenticated user cannot view edit form', function () {
        $deal = Deal::factory()->create();

        $this->get("/deals/{$deal->id}/edit")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view edit form', function () {
        $user = $this->normalUser();

        $deal = Deal::factory()->create();

        $this->actingAs($user)
            ->get("/deals/{$deal->id}/edit")
            ->assertStatus(403);
    });
});

describe('update', function () {
    test('authenticated user with permission can update a deal', function () {
        $superAdmin = $this->superAdminUser();

        $deal = Deal::factory()->create(['title' => 'Old Title']);

        $this->actingAs($superAdmin)
            ->putJson("/deals/{$deal->id}", ['title' => 'New Title'])
            ->assertStatus(200)
            ->assertJsonFragment(['title' => 'New Title']);

        $this->assertDatabaseHas('deals', [
            'id' => $deal->id,
            'title' => 'New Title',
        ]);
    });

    test('patch verb also updates a deal', function () {
        $superAdmin = $this->superAdminUser();

        $deal = Deal::factory()->create(['description' => 'Old description']);

        $this->actingAs($superAdmin)
            ->patchJson("/deals/{$deal->id}", ['description' => 'New description'])
            ->assertStatus(200)
            ->assertJsonFragment(['description' => 'New description']);
    });

    test('user without permission cannot update a deal', function () {
        $user = $this->normalUser();

        $deal = Deal::factory()->create();

        $this->actingAs($user)
            ->putJson("/deals/{$deal->id}", ['title' => 'New Title'])
            ->assertStatus(403);
    });

    test('update fails validation when pipeline_id does not exist', function () {
        $superAdmin = $this->superAdminUser();

        $deal = Deal::factory()->create();

        $this->actingAs($superAdmin)
            ->putJson("/deals/{$deal->id}", ['pipeline_id' => 99999])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['pipeline_id']);
    });

    test('update fails validation when probability is out of range', function () {
        $superAdmin = $this->superAdminUser();

        $deal = Deal::factory()->create();

        $this->actingAs($superAdmin)
            ->putJson("/deals/{$deal->id}", ['probability' => -10])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['probability']);
    });

    test('nullable fields can be cleared by passing null on update', function () {
        $superAdmin = $this->superAdminUser();
        $pipeline = Pipeline::factory()->create();
        $company = Company::factory()->create();

        $deal = Deal::factory()->create([
            'description' => 'Some description.',
            'pipeline_id' => $pipeline->id,
            'company_id' => $company->id,
            'expected_close_date' => '2026-08-01',
            'closed_at' => null,
        ]);

        $this->actingAs($superAdmin)
            ->putJson("/deals/{$deal->id}", [
                'description' => null,
                'pipeline_id' => null,
                'company_id' => null,
                'expected_close_date' => null,
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('deals', [
            'id' => $deal->id,
            'description' => null,
            'pipeline_id' => null,
            'company_id' => null,
            'expected_close_date' => null,
        ]);
    });

    test('omitted fields are not cleared on update', function () {
        $superAdmin = $this->superAdminUser();

        $deal = Deal::factory()->create([
            'description' => 'Original description.',
        ]);

        $this->actingAs($superAdmin)
            ->putJson("/deals/{$deal->id}", [
                'title' => 'Updated Title',
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('deals', [
            'id' => $deal->id,
            'title' => 'Updated Title',
            'description' => 'Original description.',
        ]);
    });

    test('patch verb can clear nullable fields', function () {
        $superAdmin = $this->superAdminUser();

        $deal = Deal::factory()->create([
            'description' => 'Some description.',
        ]);

        $this->actingAs($superAdmin)
            ->patchJson("/deals/{$deal->id}", [
                'description' => null,
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('deals', [
            'id' => $deal->id,
            'description' => null,
        ]);
    });

    test('stage can be updated to move a deal through the pipeline', function () {
        $superAdmin = $this->superAdminUser();
        $pipeline = Pipeline::factory()->create();
        $newStage = PipelineStage::factory()->create(['pipeline_id' => $pipeline->id]);

        $deal = Deal::factory()->create(['pipeline_id' => $pipeline->id]);

        $this->actingAs($superAdmin)
            ->putJson("/deals/{$deal->id}", ['stage_id' => $newStage->id])
            ->assertStatus(200);

        $this->assertDatabaseHas('deals', [
            'id' => $deal->id,
            'stage_id' => $newStage->id,
        ]);
    });

    test('logs deal updates with actor id', function () {
        $actor = $this->adminUser();

        $deal = Deal::factory()->create(['title' => 'Old Title']);

        $this->actingAs($actor)
            ->putJson("/deals/{$deal->id}", ['title' => 'New Title'])
            ->assertOk();

        $log = Log::query()
            ->where('action_id', Log::ACTION_UPDATE_DEAL)
            ->where('logged_in_user_id', $actor->id)
            ->first();

        expect($log)->not->toBeNull()
            ->and($log->data)->toHaveKeys(['before', 'after']);
    });
});

describe('destroy', function () {
    test('authenticated user with permission can soft delete a deal', function () {
        $superAdmin = $this->superAdminUser();

        $deal = Deal::factory()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/deals/{$deal->id}")
            ->assertStatus(204);

        $this->assertSoftDeleted('deals', ['id' => $deal->id]);
    });

    test('user without permission cannot soft delete a deal', function () {
        $user = $this->normalUser();

        $deal = Deal::factory()->create();

        $this->actingAs($user)
            ->deleteJson("/deals/{$deal->id}")
            ->assertStatus(403);
    });

    test('destroy returns 404 for a non-existent deal', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->deleteJson('/deals/99999')
            ->assertStatus(404);
    });
});

describe('restore', function () {
    test('authenticated user with permission can restore a soft-deleted deal', function () {
        $superAdmin = $this->superAdminUser();

        $deal = Deal::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->postJson("/deals/{$deal->id}/restore")
            ->assertStatus(204);

        $this->assertDatabaseHas('deals', [
            'id' => $deal->id,
            'deleted_at' => null,
        ]);
    });

    test('user without permission cannot restore a deal', function () {
        $user = $this->normalUser();

        $deal = Deal::factory()->deleted()->create();

        $this->actingAs($user)
            ->postJson("/deals/{$deal->id}/restore")
            ->assertStatus(403);
    });

    test('restore returns 404 for a deal that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();

        $deal = Deal::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson("/deals/{$deal->id}/restore")
            ->assertStatus(404);
    });
});

describe('force delete', function () {
    test('authenticated user with permission can force delete a deal', function () {
        $superAdmin = $this->superAdminUser();

        $deal = Deal::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/deals/{$deal->id}/force")
            ->assertStatus(204);

        $this->assertDatabaseMissing('deals', ['id' => $deal->id]);
    });

    test('user without permission cannot force delete a deal', function () {
        $user = $this->normalUser();

        $deal = Deal::factory()->deleted()->create();

        $this->actingAs($user)
            ->deleteJson("/deals/{$deal->id}/force")
            ->assertStatus(403);
    });

    test('force delete returns 404 for a deal that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();

        $deal = Deal::factory()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/deals/{$deal->id}/force")
            ->assertStatus(404);
    });
});

describe('bulk delete', function () {
    test('authenticated user with permission can bulk soft delete deals', function () {
        $superAdmin = $this->superAdminUser();

        $deals = Deal::factory()->count(3)->create();
        $ids = $deals->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson('/deals/bulk/delete', ['ids' => $ids])
            ->assertStatus(200)
            ->assertJson([
                'deleted' => $ids,
                'skipped' => [],
            ]);

        foreach ($ids as $id) {
            $this->assertSoftDeleted('deals', ['id' => $id]);
        }
    });

    test('bulk delete fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/deals/bulk/delete', ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk delete skips non-existent ids', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/deals/bulk/delete', ['ids' => [99999]])
            ->assertStatus(200)
            ->assertJson([
                'deleted' => [],
                'skipped' => [99999],
            ]);
    });

    test('user without permission cannot bulk delete deals', function () {
        $user = $this->normalUser();

        $deals = Deal::factory()->count(2)->create();

        $this->actingAs($user)
            ->postJson('/deals/bulk/delete', [
                'ids' => $deals->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('bulk restore', function () {
    test('authenticated user with permission can bulk restore deals', function () {
        $superAdmin = $this->superAdminUser();

        $deals = Deal::factory()->count(3)->deleted()->create();
        $ids = $deals->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson('/deals/bulk/restore', ['ids' => $ids])
            ->assertStatus(200)
            ->assertJson([
                'restored' => $ids,
                'skipped' => [],
            ]);

        foreach ($ids as $id) {
            $this->assertDatabaseHas('deals', [
                'id' => $id,
                'deleted_at' => null,
            ]);
        }
    });

    test('bulk restore fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/deals/bulk/restore', ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk restore skips non-existent ids', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/deals/bulk/restore', ['ids' => [99999]])
            ->assertStatus(200)
            ->assertJson([
                'restored' => [],
                'skipped' => [99999],
            ]);
    });

    test('user without permission cannot bulk restore deals', function () {
        $user = $this->normalUser();

        $deals = Deal::factory()->count(2)->deleted()->create();

        $this->actingAs($user)
            ->postJson('/deals/bulk/restore', [
                'ids' => $deals->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('soft delete scoping', function () {
    test('index does not return soft-deleted deals', function () {
        $superAdmin = $this->superAdminUser();

        Deal::factory()->count(2)->create();
        $trashed = Deal::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->get('/deals')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Deals/Index')
                ->has('deals')
            );

        $this->assertSoftDeleted('deals', ['id' => $trashed->id]);
    });

    test('show returns 404 for a soft-deleted deal', function () {
        $superAdmin = $this->superAdminUser();

        $deal = Deal::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->get("/deals/{$deal->id}")
            ->assertStatus(404);
    });
});
