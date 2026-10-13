<?php

declare(strict_types=1);

use App\Models\Course;
use App\Models\Organisation;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\CreatesUsers;

uses(
    LazilyRefreshDatabase::class,
    CreatesUsers::class,
);

describe('index', function () {
    test('index only lists courses from the current organisation', function () {
        $superAdmin = $this->superAdminUser();
        $organisation = $superAdmin->organisations()->firstOrFail();
        $otherOrganisation = Organisation::factory()->create();

        Course::factory()->count(2)->create(['organisation_id' => $organisation->id]);
        $otherOrganisation->execute(fn () => Course::factory()->count(3)->create([
            'organisation_id' => $otherOrganisation->id,
        ]));

        $this->actingAs($superAdmin)
            ->get('/courses')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Courses/Index')
                ->has('courses.data', 2)
            );
    });

    test('index does not return other organisation courses when searching', function () {
        $superAdmin = $this->superAdminUser();
        $organisation = $superAdmin->organisations()->firstOrFail();
        $otherOrganisation = Organisation::factory()->create();

        Course::factory()->create([
            'organisation_id' => $organisation->id,
            'title' => 'Shared Title Course',
        ]);
        $otherOrganisation->execute(fn () => Course::factory()->create([
            'organisation_id' => $otherOrganisation->id,
            'title' => 'Shared Title Course',
        ]));

        $this->actingAs($superAdmin)
            ->get('/courses?search=Shared+Title')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->has('courses.data', 1)
            );
    });

    test('index does not return other organisation trashed courses', function () {
        $superAdmin = $this->superAdminUser();
        $otherOrganisation = Organisation::factory()->create();

        $otherOrganisation->execute(fn () => Course::factory()->deleted()->create([
            'organisation_id' => $otherOrganisation->id,
        ]));

        $this->actingAs($superAdmin)
            ->get('/courses?trashed=only')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->has('courses.data', 0)
            );
    });
});

describe('store', function () {
    test('store assigns the course to the current organisation', function () {
        $superAdmin = $this->superAdminUser();
        $organisation = $superAdmin->organisations()->firstOrFail();

        $this->actingAs($superAdmin)
            ->postJson('/courses', [
                'title' => 'Tenant Owned Course',
                'difficulty' => 'beginner',
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('courses', [
            'title' => 'Tenant Owned Course',
            'organisation_id' => $organisation->id,
        ]);
    });

    test('store ignores a client-supplied organisation_id', function () {
        $superAdmin = $this->superAdminUser();
        $organisation = $superAdmin->organisations()->firstOrFail();
        $otherOrganisation = Organisation::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson('/courses', [
                'title' => 'Organisation Override Attempt',
                'difficulty' => 'beginner',
                'organisation_id' => $otherOrganisation->id,
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('courses', [
            'title' => 'Organisation Override Attempt',
            'organisation_id' => $organisation->id,
        ]);

        $this->assertDatabaseMissing('courses', [
            'title' => 'Organisation Override Attempt',
            'organisation_id' => $otherOrganisation->id,
        ]);
    });
});

describe('show', function () {
    test('show returns 404 for a course in another organisation', function () {
        $superAdmin = $this->superAdminUser();
        $otherOrganisation = Organisation::factory()->create();
        $course = $otherOrganisation->execute(fn () => Course::factory()->create([
            'organisation_id' => $otherOrganisation->id,
        ]));

        $this->actingAs($superAdmin)
            ->get("/courses/{$course->id}")
            ->assertStatus(404);
    });

    test('edit returns 404 for a course in another organisation', function () {
        $superAdmin = $this->superAdminUser();
        $otherOrganisation = Organisation::factory()->create();
        $course = $otherOrganisation->execute(fn () => Course::factory()->create([
            'organisation_id' => $otherOrganisation->id,
        ]));

        $this->actingAs($superAdmin)
            ->get("/courses/{$course->id}/edit")
            ->assertStatus(404);
    });
});

describe('update', function () {
    test('update returns 404 for a course in another organisation', function () {
        $superAdmin = $this->superAdminUser();
        $otherOrganisation = Organisation::factory()->create();
        $course = $otherOrganisation->execute(fn () => Course::factory()->create([
            'organisation_id' => $otherOrganisation->id,
            'title' => 'Untouchable Title',
        ]));

        $this->actingAs($superAdmin)
            ->putJson("/courses/{$course->id}", [
                'title' => 'Hijacked Title',
                'difficulty' => $course->difficulty->value,
            ])
            ->assertStatus(404);

        $this->assertDatabaseHas('courses', [
            'id' => $course->id,
            'title' => 'Untouchable Title',
        ]);
    });

    test('update cannot move a course to another organisation', function () {
        $superAdmin = $this->superAdminUser();
        $organisation = $superAdmin->organisations()->firstOrFail();
        $otherOrganisation = Organisation::factory()->create();
        $course = Course::factory()->create(['organisation_id' => $organisation->id]);

        $this->actingAs($superAdmin)
            ->patchJson("/courses/{$course->id}", [
                'title' => 'Moved Course',
                'organisation_id' => $otherOrganisation->id,
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('courses', [
            'id' => $course->id,
            'organisation_id' => $organisation->id,
        ]);
    });
});

describe('destroy', function () {
    test('destroy returns 404 for a course in another organisation', function () {
        $superAdmin = $this->superAdminUser();
        $otherOrganisation = Organisation::factory()->create();
        $course = $otherOrganisation->execute(fn () => Course::factory()->create([
            'organisation_id' => $otherOrganisation->id,
        ]));

        $this->actingAs($superAdmin)
            ->deleteJson("/courses/{$course->id}")
            ->assertStatus(404);

        $this->assertDatabaseHas('courses', [
            'id' => $course->id,
            'deleted_at' => null,
        ]);
    });

    test('restore returns 404 for a trashed course in another organisation', function () {
        $superAdmin = $this->superAdminUser();
        $otherOrganisation = Organisation::factory()->create();
        $course = $otherOrganisation->execute(fn () => Course::factory()->deleted()->create([
            'organisation_id' => $otherOrganisation->id,
        ]));

        $this->actingAs($superAdmin)
            ->postJson("/courses/{$course->id}/restore")
            ->assertStatus(404);

        $this->assertSoftDeleted('courses', ['id' => $course->id]);
    });

    test('force delete returns 404 for a trashed course in another organisation', function () {
        $superAdmin = $this->superAdminUser();
        $otherOrganisation = Organisation::factory()->create();
        $course = $otherOrganisation->execute(fn () => Course::factory()->deleted()->create([
            'organisation_id' => $otherOrganisation->id,
        ]));

        $this->actingAs($superAdmin)
            ->deleteJson("/courses/{$course->id}/force")
            ->assertStatus(404);

        $this->assertDatabaseHas('courses', ['id' => $course->id]);
    });
});

describe('bulk actions', function () {
    test('bulk delete skips ids belonging to another organisation', function () {
        $superAdmin = $this->superAdminUser();
        $organisation = $superAdmin->organisations()->firstOrFail();
        $otherOrganisation = Organisation::factory()->create();

        $own = Course::factory()->create(['organisation_id' => $organisation->id]);
        $foreign = $otherOrganisation->execute(fn () => Course::factory()->create([
            'organisation_id' => $otherOrganisation->id,
        ]));

        $this->actingAs($superAdmin)
            ->postJson('/courses/bulk/delete', ['ids' => [$own->id, $foreign->id]])
            ->assertStatus(200)
            ->assertJson([
                'deleted' => [$own->id],
                'skipped' => [$foreign->id],
            ]);

        $this->assertSoftDeleted('courses', ['id' => $own->id]);
        $this->assertDatabaseHas('courses', [
            'id' => $foreign->id,
            'deleted_at' => null,
        ]);
    });

    test('bulk restore skips ids belonging to another organisation', function () {
        $superAdmin = $this->superAdminUser();
        $organisation = $superAdmin->organisations()->firstOrFail();
        $otherOrganisation = Organisation::factory()->create();

        $own = Course::factory()->deleted()->create(['organisation_id' => $organisation->id]);
        $foreign = $otherOrganisation->execute(fn () => Course::factory()->deleted()->create([
            'organisation_id' => $otherOrganisation->id,
        ]));

        $this->actingAs($superAdmin)
            ->postJson('/courses/bulk/restore', ['ids' => [$own->id, $foreign->id]])
            ->assertStatus(200)
            ->assertJson([
                'restored' => [$own->id],
                'skipped' => [$foreign->id],
            ]);

        $this->assertDatabaseHas('courses', [
            'id' => $own->id,
            'deleted_at' => null,
        ]);
        $this->assertSoftDeleted('courses', ['id' => $foreign->id]);
    });
});

describe('export', function () {
    test('export only includes courses from the current organisation', function () {
        $superAdmin = $this->superAdminUser();
        $organisation = $superAdmin->organisations()->firstOrFail();
        $otherOrganisation = Organisation::factory()->create();

        Course::factory()->create([
            'organisation_id' => $organisation->id,
            'title' => 'Visible Export Course',
        ]);
        $otherOrganisation->execute(fn () => Course::factory()->create([
            'organisation_id' => $otherOrganisation->id,
            'title' => 'Hidden Export Course',
        ]));

        $content = $this->actingAs($superAdmin)
            ->get('/courses/export')
            ->assertStatus(200)
            ->streamedContent();

        expect($content)->toContain('Visible Export Course')
            ->and($content)->not->toContain('Hidden Export Course');
    });
});

describe('switching organisation', function () {
    test('switching organisation changes which courses are listed', function () {
        $admin = $this->adminUser();
        $firstOrganisation = $admin->organisations()->firstOrFail();
        $secondOrganisation = Organisation::factory()->create();
        $this->addUserToOrganisation($admin, $secondOrganisation);

        $firstOrganisation->execute(fn () => Course::factory()->count(1)->create([
            'organisation_id' => $firstOrganisation->id,
        ]));
        $secondOrganisation->execute(fn () => Course::factory()->count(4)->create([
            'organisation_id' => $secondOrganisation->id,
        ]));

        $this->actingAs($admin)
            ->post("/organisations/{$secondOrganisation->id}/switch")
            ->assertRedirect();

        $this->get('/courses')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Courses/Index')
                ->has('courses.data', 4)
            );
    });

    test('a course from the previous organisation is not accessible after switching', function () {
        $admin = $this->adminUser();
        $firstOrganisation = $admin->organisations()->firstOrFail();
        $secondOrganisation = Organisation::factory()->create();
        $this->addUserToOrganisation($admin, $secondOrganisation);

        $course = $firstOrganisation->execute(fn () => Course::factory()->create([
            'organisation_id' => $firstOrganisation->id,
        ]));

        $this->actingAs($admin)
            ->post("/organisations/{$secondOrganisation->id}/switch")
            ->assertRedirect();

        $this->get("/courses/{$course->id}")
            ->assertStatus(404);
    });
});

describe('no current organisation', function () {
    test('a user with no organisation is redirected to select one', function () {
        $user = $this->normalUser();

        $user->organisations()->detach();

        $this->actingAsWithoutOrganisation($user)
            ->get('/courses')
            ->assertRedirect('/organisations/select');
    });
});
