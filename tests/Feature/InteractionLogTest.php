<?php

use App\Models\Activity;
use App\Models\Company;
use App\Models\Contact;
use App\Models\InteractionLog;
use App\Models\Log;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
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

describe('store', function () {
    test('authenticated user with permission can log an interaction', function () {
        $superAdmin = $this->superAdminUser();
        $company = Company::factory()->create();

        $payload = [
            'interactable_type' => 'company',
            'interactable_id' => $company->id,
            'type' => 'call',
            'subject' => 'Discovery call',
            'outcome' => 'Client confirmed interest in the annual plan.',
            'notes' => 'Spoke for around twenty minutes.',
            'occurred_at' => now()->subDay()->toDateString(),
        ];

        $this->actingAs($superAdmin)
            ->postJson('/interaction-logs', $payload)
            ->assertStatus(201)
            ->assertJsonFragment(['type' => 'call'])
            ->assertJsonFragment(['subject' => 'Discovery call']);

        $this->assertDatabaseHas('interaction_logs', [
            'interactable_type' => Company::class,
            'interactable_id' => $company->id,
            'type' => 'call',
            'subject' => 'Discovery call',
        ]);
    });

    test('user without permission cannot log an interaction', function () {
        $user = $this->normalUser();
        $company = Company::factory()->create();

        $this->actingAs($user)
            ->postJson('/interaction-logs', [
                'interactable_type' => 'company',
                'interactable_id' => $company->id,
                'type' => 'call',
                'subject' => 'Should not be allowed',
                'occurred_at' => now()->toDateString(),
            ])
            ->assertStatus(403);
    });

    test('unauthenticated user cannot log an interaction', function () {
        $company = Company::factory()->create();

        $this->postJson('/interaction-logs', [
            'interactable_type' => 'company',
            'interactable_id' => $company->id,
            'type' => 'call',
            'subject' => 'Blocked',
            'occurred_at' => now()->toDateString(),
        ])->assertStatus(401);
    });

    test('store fails validation when interactable_type is missing', function () {
        $superAdmin = $this->superAdminUser();
        $company = Company::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson('/interaction-logs', [
                'interactable_id' => $company->id,
                'type' => 'call',
                'subject' => 'Missing type',
                'occurred_at' => now()->toDateString(),
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['interactable_type']);
    });

    test('store fails validation when interactable_type is not recognised', function () {
        $superAdmin = $this->superAdminUser();
        $company = Company::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson('/interaction-logs', [
                'interactable_type' => 'not-a-real-type',
                'interactable_id' => $company->id,
                'type' => 'call',
                'subject' => 'Invalid interactable type',
                'occurred_at' => now()->toDateString(),
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['interactable_type']);
    });

    test('store fails validation when interactable_id is missing', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/interaction-logs', [
                'interactable_type' => 'company',
                'type' => 'call',
                'subject' => 'Missing id',
                'occurred_at' => now()->toDateString(),
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['interactable_id']);
    });

    test('store fails validation when type is missing', function () {
        $superAdmin = $this->superAdminUser();
        $company = Company::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson('/interaction-logs', [
                'interactable_type' => 'company',
                'interactable_id' => $company->id,
                'subject' => 'Missing type',
                'occurred_at' => now()->toDateString(),
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['type']);
    });

    test('store fails validation when type is not a recognised enum value', function () {
        $superAdmin = $this->superAdminUser();
        $company = Company::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson('/interaction-logs', [
                'interactable_type' => 'company',
                'interactable_id' => $company->id,
                'type' => 'not-a-real-type',
                'subject' => 'Invalid type',
                'occurred_at' => now()->toDateString(),
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['type']);
    });

    test('store fails validation when subject is missing', function () {
        $superAdmin = $this->superAdminUser();
        $company = Company::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson('/interaction-logs', [
                'interactable_type' => 'company',
                'interactable_id' => $company->id,
                'type' => 'call',
                'occurred_at' => now()->toDateString(),
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['subject']);
    });

    test('store fails validation when occurred_at is missing', function () {
        $superAdmin = $this->superAdminUser();
        $company = Company::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson('/interaction-logs', [
                'interactable_type' => 'company',
                'interactable_id' => $company->id,
                'type' => 'call',
                'subject' => 'Missing occurred_at',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['occurred_at']);
    });

    test('store fails validation when occurred_at is in the future', function () {
        $superAdmin = $this->superAdminUser();
        $company = Company::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson('/interaction-logs', [
                'interactable_type' => 'company',
                'interactable_id' => $company->id,
                'type' => 'call',
                'subject' => 'Future occurred_at',
                'occurred_at' => now()->addDay()->toDateString(),
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['occurred_at']);
    });

    test('store fails validation when contact_id does not exist', function () {
        $superAdmin = $this->superAdminUser();
        $company = Company::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson('/interaction-logs', [
                'interactable_type' => 'company',
                'interactable_id' => $company->id,
                'type' => 'call',
                'subject' => 'Invalid contact',
                'occurred_at' => now()->toDateString(),
                'contact_id' => 99999,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['contact_id']);
    });

    test('store succeeds with only required fields', function () {
        $superAdmin = $this->superAdminUser();
        $company = Company::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson('/interaction-logs', [
                'interactable_type' => 'company',
                'interactable_id' => $company->id,
                'type' => 'email',
                'subject' => 'Minimal log',
                'occurred_at' => now()->toDateString(),
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('interaction_logs', [
            'interactable_id' => $company->id,
            'type' => 'email',
            'subject' => 'Minimal log',
            'outcome' => null,
            'notes' => null,
            'contact_id' => null,
        ]);
    });

    test('store succeeds against a contact and can log with the same contact_id', function () {
        $superAdmin = $this->superAdminUser();
        $company = Company::factory()->create();
        $contact = Contact::factory()->forModel($company)->create();

        $this->actingAs($superAdmin)
            ->postJson('/interaction-logs', [
                'interactable_type' => 'contact',
                'interactable_id' => $contact->id,
                'type' => 'call',
                'subject' => 'Follow-up with contact',
                'occurred_at' => now()->toDateString(),
                'contact_id' => $contact->id,
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('interaction_logs', [
            'interactable_type' => Contact::class,
            'interactable_id' => $contact->id,
            'contact_id' => $contact->id,
        ]);
    });

    test('logging an interaction creates a linked activity timeline entry', function () {
        $superAdmin = $this->superAdminUser();
        $company = Company::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson('/interaction-logs', [
                'interactable_type' => 'company',
                'interactable_id' => $company->id,
                'type' => 'call',
                'subject' => 'Contract terms follow-up',
                'occurred_at' => now()->toDateString(),
            ])
            ->assertStatus(201);

        $activity = Activity::query()
            ->where('activityable_type', Company::class)
            ->where('activityable_id', $company->id)
            ->where('type', 'call_logged')
            ->first();

        expect($activity)->not->toBeNull()
            ->and($activity->description)->toContain('Contract terms follow-up');
    });

    test('logging an email interaction creates an email_logged activity entry', function () {
        $superAdmin = $this->superAdminUser();
        $company = Company::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson('/interaction-logs', [
                'interactable_type' => 'company',
                'interactable_id' => $company->id,
                'type' => 'email',
                'subject' => 'Renewal reminder',
                'occurred_at' => now()->toDateString(),
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('activities', [
            'activityable_type' => Company::class,
            'activityable_id' => $company->id,
            'type' => 'email_logged',
        ]);
    });

    test('logs interaction log creation with actor id', function () {
        $actor = $this->adminUser();
        $company = Company::factory()->create();

        $this->actingAs($actor)
            ->postJson('/interaction-logs', [
                'interactable_type' => 'company',
                'interactable_id' => $company->id,
                'type' => 'call',
                'subject' => 'Logged via test',
                'occurred_at' => now()->toDateString(),
            ])
            ->assertCreated();

        $log = Log::query()
            ->where('action_id', Log::ACTION_CREATE_INTERACTION_LOG)
            ->where('logged_in_user_id', $actor->id)
            ->first();

        expect($log)->not->toBeNull()
            ->and($log->data)->toHaveKey('after');
    });
});

describe('update', function () {
    test('authenticated user with permission can update an interaction log', function () {
        $superAdmin = $this->superAdminUser();
        $company = Company::factory()->create();

        $interactionLog = InteractionLog::factory()->forModel($company)->create(['subject' => 'Old subject']);

        $this->actingAs($superAdmin)
            ->putJson("/interaction-logs/{$interactionLog->id}", ['subject' => 'New subject'])
            ->assertStatus(200)
            ->assertJsonFragment(['subject' => 'New subject']);

        $this->assertDatabaseHas('interaction_logs', [
            'id' => $interactionLog->id,
            'subject' => 'New subject',
        ]);
    });

    test('patch verb also updates an interaction log', function () {
        $superAdmin = $this->superAdminUser();
        $company = Company::factory()->create();

        $interactionLog = InteractionLog::factory()->forModel($company)->create(['outcome' => 'Old outcome']);

        $this->actingAs($superAdmin)
            ->patchJson("/interaction-logs/{$interactionLog->id}", ['outcome' => 'New outcome'])
            ->assertStatus(200)
            ->assertJsonFragment(['outcome' => 'New outcome']);
    });

    test('user without permission cannot update an interaction log', function () {
        $superAdmin = $this->superAdminUser();
        $user = $this->normalUser();
        $company = Company::factory()->create();

        $interactionLog = InteractionLog::factory()->forModel($company)->create();

        $this->actingAs($user)
            ->putJson("/interaction-logs/{$interactionLog->id}", ['subject' => 'Blocked'])
            ->assertStatus(403);
    });

    test('update fails validation when type is not a recognised enum value', function () {
        $superAdmin = $this->superAdminUser();
        $company = Company::factory()->create();

        $interactionLog = InteractionLog::factory()->forModel($company)->create();

        $this->actingAs($superAdmin)
            ->putJson("/interaction-logs/{$interactionLog->id}", ['type' => 'not-a-real-type'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['type']);
    });

    test('update fails validation when subject is cleared to empty', function () {
        $superAdmin = $this->superAdminUser();
        $company = Company::factory()->create();

        $interactionLog = InteractionLog::factory()->forModel($company)->create();

        $this->actingAs($superAdmin)
            ->putJson("/interaction-logs/{$interactionLog->id}", ['subject' => ''])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['subject']);
    });

    test('update fails validation when occurred_at is in the future', function () {
        $superAdmin = $this->superAdminUser();
        $company = Company::factory()->create();

        $interactionLog = InteractionLog::factory()->forModel($company)->create();

        $this->actingAs($superAdmin)
            ->putJson("/interaction-logs/{$interactionLog->id}", [
                'occurred_at' => now()->addDay()->toDateString(),
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['occurred_at']);
    });

    test('update fails validation when contact_id does not exist', function () {
        $superAdmin = $this->superAdminUser();
        $company = Company::factory()->create();

        $interactionLog = InteractionLog::factory()->forModel($company)->create();

        $this->actingAs($superAdmin)
            ->putJson("/interaction-logs/{$interactionLog->id}", ['contact_id' => 99999])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['contact_id']);
    });

    test('interactable_type and interactable_id cannot be changed via update', function () {
        $superAdmin = $this->superAdminUser();
        $company = Company::factory()->create();
        $otherCompany = Company::factory()->create();

        $interactionLog = InteractionLog::factory()->forModel($company)->create();

        $this->actingAs($superAdmin)
            ->putJson("/interaction-logs/{$interactionLog->id}", [
                'interactable_type' => 'company',
                'interactable_id' => $otherCompany->id,
                'subject' => 'Attempted re-point',
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('interaction_logs', [
            'id' => $interactionLog->id,
            'interactable_type' => Company::class,
            'interactable_id' => $company->id,
            'subject' => 'Attempted re-point',
        ]);
    });

    test('nullable fields can be cleared by passing null on update', function () {
        $superAdmin = $this->superAdminUser();
        $company = Company::factory()->create();

        $interactionLog = InteractionLog::factory()->forModel($company)->create([
            'outcome' => 'Some outcome',
            'notes' => 'Some notes',
        ]);

        $this->actingAs($superAdmin)
            ->putJson("/interaction-logs/{$interactionLog->id}", [
                'outcome' => null,
                'notes' => null,
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('interaction_logs', [
            'id' => $interactionLog->id,
            'outcome' => null,
            'notes' => null,
        ]);
    });

    test('omitted fields are not cleared on update', function () {
        $superAdmin = $this->superAdminUser();
        $company = Company::factory()->create();

        $interactionLog = InteractionLog::factory()->forModel($company)->create([
            'outcome' => 'Original outcome',
            'notes' => 'Original notes',
        ]);

        $this->actingAs($superAdmin)
            ->putJson("/interaction-logs/{$interactionLog->id}", ['subject' => 'Updated subject'])
            ->assertStatus(200);

        $this->assertDatabaseHas('interaction_logs', [
            'id' => $interactionLog->id,
            'subject' => 'Updated subject',
            'outcome' => 'Original outcome',
            'notes' => 'Original notes',
        ]);
    });

    test('logs interaction log updates with actor id', function () {
        $actor = $this->adminUser();
        $company = Company::factory()->create();

        $interactionLog = InteractionLog::factory()->forModel($company)->create(['subject' => 'Old subject']);

        $this->actingAs($actor)
            ->putJson("/interaction-logs/{$interactionLog->id}", ['subject' => 'New subject'])
            ->assertOk();

        $log = Log::query()
            ->where('action_id', Log::ACTION_UPDATE_INTERACTION_LOG)
            ->where('logged_in_user_id', $actor->id)
            ->first();

        expect($log)->not->toBeNull()
            ->and($log->data)->toHaveKeys(['before', 'after']);
    });
});

describe('destroy', function () {
    test('authenticated user with permission can soft delete an interaction log', function () {
        $superAdmin = $this->superAdminUser();
        $company = Company::factory()->create();

        $interactionLog = InteractionLog::factory()->forModel($company)->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/interaction-logs/{$interactionLog->id}")
            ->assertStatus(200);

        $this->assertSoftDeleted('interaction_logs', ['id' => $interactionLog->id]);
    });

    test('user without permission cannot soft delete an interaction log', function () {
        $superAdmin = $this->superAdminUser();
        $user = $this->normalUser();
        $company = Company::factory()->create();

        $interactionLog = InteractionLog::factory()->forModel($company)->create();

        $this->actingAs($user)
            ->deleteJson("/interaction-logs/{$interactionLog->id}")
            ->assertStatus(403);
    });

    test('destroy returns 404 for non-existent interaction log', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->deleteJson('/interaction-logs/99999')
            ->assertStatus(404);
    });

    test('logs interaction log deletion with actor id', function () {
        $actor = $this->adminUser();
        $company = Company::factory()->create();

        $interactionLog = InteractionLog::factory()->forModel($company)->create();

        $this->actingAs($actor)
            ->deleteJson("/interaction-logs/{$interactionLog->id}")
            ->assertOk();

        $log = Log::query()
            ->where('action_id', Log::ACTION_DELETE_INTERACTION_LOG)
            ->where('logged_in_user_id', $actor->id)
            ->first();

        expect($log)->not->toBeNull();
    });
});

describe('force delete', function () {
    test('authenticated admin can force delete an interaction log', function () {
        $admin = $this->adminUser();
        $company = Company::factory()->create();

        $interactionLog = InteractionLog::factory()->forModel($company)->deleted()->create();

        $this->actingAs($admin)
            ->deleteJson("/interaction-logs/{$interactionLog->id}/force")
            ->assertStatus(200);

        $this->assertDatabaseMissing('interaction_logs', ['id' => $interactionLog->id]);
    });

    test('user without permission cannot force delete an interaction log', function () {
        $superAdmin = $this->superAdminUser();
        $user = $this->normalUser();
        $company = Company::factory()->create();

        $interactionLog = InteractionLog::factory()->forModel($company)->deleted()->create();

        $this->actingAs($user)
            ->deleteJson("/interaction-logs/{$interactionLog->id}/force")
            ->assertStatus(403);
    });

    test('force delete returns 404 for an interaction log that is not soft-deleted', function () {
        $admin = $this->adminUser();
        $company = Company::factory()->create();

        $interactionLog = InteractionLog::factory()->forModel($company)->create();

        $this->actingAs($admin)
            ->deleteJson("/interaction-logs/{$interactionLog->id}/force")
            ->assertStatus(404);
    });
});
