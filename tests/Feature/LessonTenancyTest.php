<?php

declare(strict_types=1);

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Organisation;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\CreatesUsers;

uses(
    LazilyRefreshDatabase::class,
    CreatesUsers::class,
);

/**
 * Create a published lesson that belongs to a second organisation.
 *
 * The data is built while organisation B is the current tenant, because
 * BelongsToOrganisation refuses to create records for any other organisation.
 * The course relation is attached here, as it cannot be loaded from tenant A.
 *
 * @param  array<string, mixed>  $overrides
 */
function organisationBLesson(object $test, array $overrides = []): Lesson
{
    return $test->inOrganisation(
        $test->otherOrganisation(),
        function (Organisation $organisationB) use ($overrides): Lesson {
            $course = Course::factory()->create([
                'organisation_id' => $organisationB->id,
                'published_at' => now()->subDay(),
            ]);

            $lesson = Lesson::factory()->create(array_merge([
                'course_id' => $course->id,
                'organisation_id' => $organisationB->id,
                'title' => 'Organisation B secret lesson',
                'published_at' => now()->subDay(),
            ], $overrides));

            $lesson->setRelation('course', $course);

            return $lesson;
        }
    );
}

/**
 * Read a lesson ignoring the tenant and trash scopes.
 */
function rawLesson(int $id): ?Lesson
{
    return Lesson::withoutGlobalScopes()->find($id);
}

describe('read access', function () {
    test('show returns 404 for a lesson from another organisation', function () {
        $admin = $this->adminUser();
        $foreign = organisationBLesson($this);

        $this->actingAs($admin)
            ->get("/lessons/{$foreign->id}")
            ->assertStatus(404);
    });

    test('edit returns 404 for a lesson from another organisation', function () {
        $admin = $this->adminUser();
        $foreign = organisationBLesson($this);

        $this->actingAs($admin)
            ->get("/lessons/{$foreign->id}/edit")
            ->assertStatus(404);
    });

    test('a learner gets 404 for a published lesson from another organisation', function () {
        $learner = $this->learnerUser();
        $foreign = organisationBLesson($this);

        $this->actingAs($learner)
            ->get("/lessons/{$foreign->id}")
            ->assertStatus(404);
    });

    test('the index never lists lessons from another organisation', function () {
        $admin = $this->adminUser();
        $own = Lesson::factory()->create();
        organisationBLesson($this);

        $this->actingAs($admin)
            ->get('/lessons')
            ->assertInertia(fn (Assert $page) => $page
                ->has('lessons.data', 1)
                ->where('lessons.data.0.id', $own->id)
            );
    });

    test('the course options never include courses from another organisation', function () {
        $admin = $this->adminUser();
        $own = Course::factory()->create();
        $foreign = organisationBLesson($this)->course;

        $this->actingAs($admin)
            ->get('/lessons/create')
            ->assertInertia(fn (Assert $page) => $page
                ->where('course_options', fn ($options) => collect($options)->pluck('id')->contains($own->id)
                    && ! collect($options)->pluck('id')->contains($foreign->id))
            );
    });

    test('a course_id query string for another organisation is not pre-selected', function () {
        $admin = $this->adminUser();
        $foreign = organisationBLesson($this)->course;

        $this->actingAs($admin)
            ->get("/lessons/create?course_id={$foreign->id}")
            ->assertInertia(fn (Assert $page) => $page->where('selected_course_id', null));
    });

    test('the export never contains lessons from another organisation', function () {
        $admin = $this->adminUser();
        Lesson::factory()->create(['title' => 'Organisation A lesson']);
        organisationBLesson($this);

        $csv = $this->actingAs($admin)
            ->get('/lessons/export')
            ->assertStatus(200)
            ->streamedContent();

        expect($csv)->toContain('Organisation A lesson')
            ->and($csv)->not->toContain('Organisation B secret lesson');
    });
});

describe('write access', function () {
    test('update returns 404 for a lesson from another organisation', function () {
        $admin = $this->adminUser();
        $foreign = organisationBLesson($this);

        $this->actingAs($admin)
            ->putJson("/lessons/{$foreign->id}", ['title' => 'Hijacked'])
            ->assertStatus(404);

        expect(rawLesson($foreign->id)->title)->toBe('Organisation B secret lesson');
    });

    test('destroy returns 404 for a lesson from another organisation', function () {
        $admin = $this->adminUser();
        $foreign = organisationBLesson($this);

        $this->actingAs($admin)
            ->deleteJson("/lessons/{$foreign->id}")
            ->assertStatus(404);

        $this->assertNotSoftDeleted('lessons', ['id' => $foreign->id]);
    });

    test('restore returns 404 for a trashed lesson from another organisation', function () {
        $admin = $this->adminUser();
        $foreign = organisationBLesson($this, ['deleted_at' => now()]);

        $this->actingAs($admin)
            ->postJson("/lessons/{$foreign->id}/restore")
            ->assertStatus(404);

        $this->assertSoftDeleted('lessons', ['id' => $foreign->id]);
    });

    test('force delete returns 404 for a trashed lesson from another organisation', function () {
        $admin = $this->superAdminUser();
        $foreign = organisationBLesson($this, ['deleted_at' => now()]);

        $this->actingAs($admin)
            ->deleteJson("/lessons/{$foreign->id}/force")
            ->assertStatus(404);

        $this->assertDatabaseHas('lessons', ['id' => $foreign->id]);
    });

    test('bulk delete skips ids from another organisation and leaves them untouched', function () {
        $admin = $this->adminUser();
        $own = Lesson::factory()->create();
        $foreign = organisationBLesson($this);

        $this->actingAs($admin)
            ->postJson('/lessons/bulk/delete', ['ids' => [$own->id, $foreign->id]])
            ->assertStatus(200)
            ->assertJson([
                'deleted' => [$own->id],
                'skipped' => [$foreign->id],
            ]);

        $this->assertSoftDeleted('lessons', ['id' => $own->id]);
        $this->assertNotSoftDeleted('lessons', ['id' => $foreign->id]);
    });

    test('bulk restore skips trashed ids from another organisation', function () {
        $admin = $this->adminUser();
        $own = Lesson::factory()->deleted()->create();
        $foreign = organisationBLesson($this, ['deleted_at' => now()]);

        $this->actingAs($admin)
            ->postJson('/lessons/bulk/restore', ['ids' => [$own->id, $foreign->id]])
            ->assertStatus(200)
            ->assertJson([
                'restored' => [$own->id],
                'skipped' => [$foreign->id],
            ]);

        $this->assertSoftDeleted('lessons', ['id' => $foreign->id]);
    });

    test('creating a lesson in a course from another organisation is rejected and nothing is written', function () {
        $admin = $this->adminUser();
        $foreignCourse = organisationBLesson($this)->course;
        $before = Lesson::withoutGlobalScopes()->count();

        $this->actingAs($admin)
            ->postJson('/lessons', [
                'course_id' => $foreignCourse->id,
                'title' => 'Cross tenant write',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['course_id']);

        expect(Lesson::withoutGlobalScopes()->count())->toBe($before);
    });
});
