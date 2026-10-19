<?php

declare(strict_types=1);

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Log;
use App\Models\Organisation;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Collection;
use Tests\Concerns\CreatesUsers;

uses(
    LazilyRefreshDatabase::class,
    CreatesUsers::class,
);

/**
 * Create a course with lessons at positions 1..$count.
 *
 * Call this after the acting user exists so the organisation is current.
 *
 * @return array{0: Course, 1: Collection<int, Lesson>}
 */
function reorderCourseWithLessons(int $count = 3): array
{
    $course = Course::factory()->create(['published_at' => now()->subDay()]);

    $lessons = collect(range(1, $count))->map(
        fn (int $position): Lesson => Lesson::factory()->create([
            'course_id' => $course->id,
            'position' => $position,
        ])
    );

    return [$course, $lessons];
}

/**
 * Snapshot every lesson position, ignoring tenant and trash scopes.
 *
 * @return array<int, int>
 */
function lessonPositions(): array
{
    return Lesson::withoutGlobalScopes()->orderBy('id')->pluck('position', 'id')->all();
}

describe('access', function () {
    test('unauthenticated user cannot reorder', function () {
        $this->postJson('/lessons/reorder', ['course_id' => 1, 'lesson_ids' => [1]])
            ->assertStatus(401);
    });

    test('a learner receives 403', function () {
        $learner = $this->learnerUser();
        [$course, $lessons] = reorderCourseWithLessons();

        $before = lessonPositions();

        $this->actingAs($learner)
            ->postJson('/lessons/reorder', [
                'course_id' => $course->id,
                'lesson_ids' => $lessons->pluck('id')->reverse()->values()->all(),
            ])
            ->assertStatus(403);

        expect(lessonPositions())->toBe($before);
    });

    test('a user without any lesson permissions receives 403', function () {
        $user = $this->userWithNoPermissions();
        [$course, $lessons] = reorderCourseWithLessons();

        $this->actingAs($user)
            ->postJson('/lessons/reorder', [
                'course_id' => $course->id,
                'lesson_ids' => $lessons->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('valid order', function () {
    test('a valid order is saved', function () {
        $admin = $this->adminUser();
        [$course, $lessons] = reorderCourseWithLessons();

        $order = [$lessons[2]->id, $lessons[0]->id, $lessons[1]->id];

        $this->actingAs($admin)
            ->postJson('/lessons/reorder', ['course_id' => $course->id, 'lesson_ids' => $order])
            ->assertOk()
            ->assertJson(['message' => 'Lessons reordered.']);

        expect($lessons[2]->fresh()->position)->toBe(1)
            ->and($lessons[0]->fresh()->position)->toBe(2)
            ->and($lessons[1]->fresh()->position)->toBe(3);
    });

    test('updated_by is recorded on every reordered lesson', function () {
        $admin = $this->adminUser();
        [$course, $lessons] = reorderCourseWithLessons();

        $this->actingAs($admin)
            ->postJson('/lessons/reorder', [
                'course_id' => $course->id,
                'lesson_ids' => $lessons->pluck('id')->reverse()->values()->all(),
            ])
            ->assertOk();

        foreach ($lessons as $lesson) {
            expect($lesson->fresh()->updated_by)->toBe($admin->id);
        }
    });

    test('a reorder is audited with the new order', function () {
        $admin = $this->adminUser();
        [$course, $lessons] = reorderCourseWithLessons();

        $order = [$lessons[1]->id, $lessons[2]->id, $lessons[0]->id];

        $this->actingAs($admin)
            ->postJson('/lessons/reorder', ['course_id' => $course->id, 'lesson_ids' => $order])
            ->assertOk();

        $log = Log::query()
            ->where('action_id', Log::ACTION_REORDER_LESSON)
            ->where('logged_in_user_id', $admin->id)
            ->first();

        expect($log)->not->toBeNull()
            ->and($log->data['after']['lesson_ids'])->toEqual($order);
    });

    test('an editor with edit lessons can reorder', function () {
        $editor = $this->lessonEditorUser();
        [$course, $lessons] = reorderCourseWithLessons(2);

        $this->actingAs($editor)
            ->postJson('/lessons/reorder', [
                'course_id' => $course->id,
                'lesson_ids' => [$lessons[1]->id, $lessons[0]->id],
            ])
            ->assertOk();

        expect($lessons[1]->fresh()->position)->toBe(1);
    });

    test('trashed lessons are not part of the order', function () {
        $admin = $this->adminUser();
        [$course, $lessons] = reorderCourseWithLessons(2);
        Lesson::factory()->deleted()->create(['course_id' => $course->id, 'position' => 3]);

        $this->actingAs($admin)
            ->postJson('/lessons/reorder', [
                'course_id' => $course->id,
                'lesson_ids' => [$lessons[1]->id, $lessons[0]->id],
            ])
            ->assertOk();

        expect($lessons[1]->fresh()->position)->toBe(1)
            ->and($lessons[0]->fresh()->position)->toBe(2);
    });

    test('a request that fully covered the course before another lesson was added is rejected', function () {
        $admin = $this->adminUser();
        [$course, $lessons] = reorderCourseWithLessons(2);
        $staleOrder = $lessons->pluck('id')->reverse()->values()->all();

        Lesson::factory()->create(['course_id' => $course->id, 'position' => 3]);

        $before = lessonPositions();

        $this->actingAs($admin)
            ->postJson('/lessons/reorder', ['course_id' => $course->id, 'lesson_ids' => $staleOrder])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['lesson_ids']);

        expect(lessonPositions())->toBe($before);
    });
});

describe('rejected orders', function () {
    test('a missing id is rejected and nothing changes', function () {
        $admin = $this->adminUser();
        [$course, $lessons] = reorderCourseWithLessons();
        $before = lessonPositions();

        $this->actingAs($admin)
            ->postJson('/lessons/reorder', [
                'course_id' => $course->id,
                'lesson_ids' => [$lessons[1]->id, $lessons[0]->id],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['lesson_ids']);

        expect(lessonPositions())->toBe($before);
    });

    test('a duplicate id is rejected and nothing changes', function () {
        $admin = $this->adminUser();
        [$course, $lessons] = reorderCourseWithLessons();
        $before = lessonPositions();

        $this->actingAs($admin)
            ->postJson('/lessons/reorder', [
                'course_id' => $course->id,
                'lesson_ids' => [$lessons[0]->id, $lessons[0]->id, $lessons[1]->id],
            ])
            ->assertStatus(422);

        expect(lessonPositions())->toBe($before);
    });

    test('an id from another course in the same organisation is rejected', function () {
        $admin = $this->adminUser();
        [$course, $lessons] = reorderCourseWithLessons(2);
        $otherCourseLesson = Lesson::factory()->create(['position' => 1]);
        $before = lessonPositions();

        $this->actingAs($admin)
            ->postJson('/lessons/reorder', [
                'course_id' => $course->id,
                'lesson_ids' => [$lessons[0]->id, $otherCourseLesson->id],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['lesson_ids']);

        expect(lessonPositions())->toBe($before);
    });

    test('an id from another organisation is rejected and its position is untouched', function () {
        $admin = $this->adminUser();
        [$course, $lessons] = reorderCourseWithLessons(2);

        $foreignLesson = $this->inOrganisation(
            $this->otherOrganisation(),
            function (Organisation $organisationB): Lesson {
                $foreignCourse = Course::factory()->create(['organisation_id' => $organisationB->id]);

                return Lesson::factory()->create([
                    'course_id' => $foreignCourse->id,
                    'organisation_id' => $organisationB->id,
                    'position' => 7,
                ]);
            },
        );
        $before = lessonPositions();

        $this->actingAs($admin)
            ->postJson('/lessons/reorder', [
                'course_id' => $course->id,
                'lesson_ids' => [$lessons[0]->id, $foreignLesson->id],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['lesson_ids']);

        expect(lessonPositions())->toBe($before)
            ->and(Lesson::withoutGlobalScopes()->find($foreignLesson->id)->position)->toBe(7);
    });

    test('a course from another organisation is rejected', function () {
        $admin = $this->adminUser();

        $foreignLesson = $this->inOrganisation(
            $this->otherOrganisation(),
            function (Organisation $organisationB): Lesson {
                $foreignCourse = Course::factory()->create(['organisation_id' => $organisationB->id]);

                return Lesson::factory()->create([
                    'course_id' => $foreignCourse->id,
                    'organisation_id' => $organisationB->id,
                    'position' => 1,
                ]);
            },
        );

        // The request resolves the course through the tenant scope, so it may
        // deny (403) before validation (422). Either way nothing is reordered.
        $response = $this->actingAs($admin)
            ->postJson('/lessons/reorder', [
                'course_id' => $foreignLesson->course_id,
                'lesson_ids' => [$foreignLesson->id],
            ]);

        expect($response->status())->toBeIn([403, 422])
            ->and(Lesson::withoutGlobalScopes()->find($foreignLesson->id)->position)->toBe(1);
    });

    test('a trashed lesson id is rejected', function () {
        $admin = $this->adminUser();
        [$course, $lessons] = reorderCourseWithLessons(2);
        $trashed = Lesson::factory()->deleted()->create(['course_id' => $course->id, 'position' => 3]);

        $this->actingAs($admin)
            ->postJson('/lessons/reorder', [
                'course_id' => $course->id,
                'lesson_ids' => [$lessons[0]->id, $lessons[1]->id, $trashed->id],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['lesson_ids']);
    });

    test('an empty order fails validation', function () {
        $admin = $this->adminUser();
        [$course] = reorderCourseWithLessons();

        $this->actingAs($admin)
            ->postJson('/lessons/reorder', ['course_id' => $course->id, 'lesson_ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['lesson_ids']);
    });

    test('non-integer ids fail validation', function () {
        $admin = $this->adminUser();
        [$course] = reorderCourseWithLessons();

        $this->actingAs($admin)
            ->postJson('/lessons/reorder', [
                'course_id' => $course->id,
                'lesson_ids' => ['one', 'two'],
            ])
            ->assertStatus(422);
    });

    test('a rejected reorder writes no audit entry', function () {
        $admin = $this->adminUser();
        [$course, $lessons] = reorderCourseWithLessons();

        $this->actingAs($admin)
            ->postJson('/lessons/reorder', [
                'course_id' => $course->id,
                'lesson_ids' => [$lessons[0]->id],
            ])
            ->assertStatus(422);

        expect(Log::query()->where('action_id', Log::ACTION_REORDER_LESSON)->exists())->toBeFalse();
    });
});
