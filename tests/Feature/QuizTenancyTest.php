<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\CreatesQuizzes;
use Tests\Concerns\CreatesUsers;

uses(
    LazilyRefreshDatabase::class,
    CreatesUsers::class,
    CreatesQuizzes::class,
);

describe('quiz access', function () {
    test('a quiz from another organisation returns 404 on show', function () {
        $admin = $this->adminUser();
        $quiz = $this->foreignQuiz();

        $this->actingAs($admin)
            ->get("/quizzes/{$quiz->id}")
            ->assertStatus(404);
    });

    test('a quiz from another organisation returns 404 on edit', function () {
        $admin = $this->adminUser();
        $quiz = $this->foreignQuiz();

        $this->actingAs($admin)
            ->get("/quizzes/{$quiz->id}/edit")
            ->assertStatus(404);
    });

    test('a quiz from another organisation cannot be updated', function () {
        $admin = $this->adminUser();
        $quiz = $this->foreignQuiz(['title' => 'Untouched']);
        $question = $this->questionIn($this->testOrganisation->id);

        $this->actingAs($admin)
            ->putJson("/quizzes/{$quiz->id}", $this->quizPayload($this->courseFor($admin), [
                ['question_id' => $question->id, 'points' => null],
            ], ['title' => 'Hijacked']))
            ->assertStatus(404);

        $this->assertDatabaseHas('quizzes', ['id' => $quiz->id, 'title' => 'Untouched']);
    });

    test('a quiz from another organisation cannot be deleted', function () {
        $admin = $this->adminUser();
        $quiz = $this->foreignQuiz();

        $this->actingAs($admin)
            ->deleteJson("/quizzes/{$quiz->id}")
            ->assertStatus(404);

        $this->assertDatabaseHas('quizzes', ['id' => $quiz->id, 'deleted_at' => null]);
    });

    test('index only returns quizzes from the users own organisation', function () {
        $admin = $this->adminUser();

        $this->quizFor($admin, ['title' => 'Mine']);
        $this->foreignQuiz(['title' => 'Not Mine']);

        $this->actingAs($admin)
            ->get('/quizzes')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Quizzes/Index')
                ->where('quizzes.meta.total', 1)
                ->where('quizzes.data.0.title', 'Mine')
            );
    });

    test('bulk delete ignores quizzes from another organisation', function () {
        $admin = $this->adminUser();
        $quiz = $this->foreignQuiz();

        $this->actingAs($admin)
            ->postJson('/quizzes/bulk/delete', ['ids' => [$quiz->id]])
            ->assertStatus(200)
            ->assertJson(['deleted' => [], 'skipped' => [$quiz->id]]);

        $this->assertDatabaseHas('quizzes', ['id' => $quiz->id, 'deleted_at' => null]);
    });
});

describe('course and lesson ownership', function () {
    test('course_id from another organisation is rejected', function () {
        $admin = $this->adminUser();
        $otherCourse = $this->foreignCourse();
        $question = $this->questionIn($this->testOrganisation->id);

        $this->actingAs($admin)
            ->postJson('/quizzes', $this->quizPayload($otherCourse, [
                ['question_id' => $question->id, 'points' => null],
            ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['course_id']);

        $this->assertDatabaseMissing('quizzes', ['title' => 'Laravel Basics Quiz']);
    });

    test('a trashed course is rejected', function () {
        $admin = $this->adminUser();
        $course = $this->courseFor($admin);
        $course->delete();
        $question = $this->questionIn($this->testOrganisation->id);

        $this->actingAs($admin)
            ->postJson('/quizzes', $this->quizPayload($course, [
                ['question_id' => $question->id, 'points' => null],
            ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['course_id']);
    });

    test('lesson_id from a different course is rejected', function () {
        $admin = $this->adminUser();
        $course = $this->courseFor($admin);
        $otherCourse = $this->courseFor($admin);
        $lesson = $this->lessonFor($otherCourse);
        $question = $this->questionIn($this->testOrganisation->id);

        $this->actingAs($admin)
            ->postJson('/quizzes', $this->quizPayload($course, [
                ['question_id' => $question->id, 'points' => null],
            ], ['lesson_id' => $lesson->id]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['lesson_id']);
    });

    test('lesson_id from another organisation is rejected', function () {
        $admin = $this->adminUser();
        $course = $this->courseFor($admin);
        $lesson = $this->foreignLesson();
        $question = $this->questionIn($this->testOrganisation->id);

        $this->actingAs($admin)
            ->postJson('/quizzes', $this->quizPayload($course, [
                ['question_id' => $question->id, 'points' => null],
            ], ['lesson_id' => $lesson->id]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['lesson_id']);
    });

    test('a lesson belonging to the chosen course is accepted', function () {
        $admin = $this->adminUser();
        $course = $this->courseFor($admin);
        $lesson = $this->lessonFor($course);
        $question = $this->questionIn($this->testOrganisation->id);

        $this->actingAs($admin)
            ->postJson('/quizzes', $this->quizPayload($course, [
                ['question_id' => $question->id, 'points' => null],
            ], ['lesson_id' => $lesson->id]))
            ->assertStatus(201);

        $this->assertDatabaseHas('quizzes', [
            'title' => 'Laravel Basics Quiz',
            'lesson_id' => $lesson->id,
        ]);
    });
});

describe('course lessons endpoint', function () {
    test('course-lessons returns the lessons for a course in the users organisation', function () {
        $admin = $this->adminUser();
        $course = $this->courseFor($admin);
        $lesson = $this->lessonFor($course, ['title' => 'Intro']);

        $this->actingAs($admin)
            ->getJson("/quizzes/course-lessons?course_id={$course->id}")
            ->assertOk()
            ->assertJsonPath('0.id', $lesson->id)
            ->assertJsonPath('0.title', 'Intro');
    });

    test('course-lessons returns an empty list for a course in another organisation', function () {
        $admin = $this->adminUser();
        $foreignLesson = $this->foreignLesson();

        $this->actingAs($admin)
            ->getJson("/quizzes/course-lessons?course_id={$foreignLesson->course_id}")
            ->assertOk()
            ->assertExactJson([]);
    });

    test('unauthenticated user cannot call course-lessons', function () {
        $this->getJson('/quizzes/course-lessons?course_id=1')
            ->assertUnauthorized();
    });

    test('user without permission cannot call course-lessons', function () {
        $user = $this->userWithNoPermissions();
        $course = $this->courseFor($user);

        $this->actingAs($user)
            ->getJson("/quizzes/course-lessons?course_id={$course->id}")
            ->assertStatus(403);
    });
});
