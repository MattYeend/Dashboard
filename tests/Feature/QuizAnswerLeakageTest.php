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

beforeEach(function () {
    /** @var \Tests\TestCase&\Tests\Concerns\CreatesUsers&\Tests\Concerns\CreatesQuizzes $this */
    $this->editor = $this->adminUser();

    $this->learner = $this->userWithPermissions([
        'view any quizzes',
        'view quizzes',
        'view any course',
        'view course',
    ]);

    $this->quiz = $this->quizFor($this->editor, [], 2);
});

describe('learner view', function () {
    test('a learner never receives is_correct or explanation on show', function () {
        $response = $this->actingAs($this->learner)
            ->get("/quizzes/{$this->quiz->id}")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Quizzes/Show')
                ->where('canEdit', false)
                ->has('quiz.questions', 2)
                ->missing('quiz.questions.0.explanation')
                ->missing('quiz.questions.0.default_points')
                ->missing('quiz.questions.0.answers.0.is_correct')
                ->missing('quiz.questions.0.answers.0.feedback')
            );

        $encoded = json_encode($this->inertiaProps($response));

        expect($encoded)
            ->not->toContain('is_correct')
            ->not->toContain('Secret explanation')
            ->not->toContain('feedback');
    });

    test('a learner never receives is_correct or explanation in a JSON response', function () {
        $response = $this->actingAs($this->learner)
            ->getJson("/quizzes/{$this->quiz->id}")
            ->assertStatus(200);

        expect($response->getContent())
            ->not->toContain('is_correct')
            ->not->toContain('Secret explanation');
    });

    test('a learner never receives answer data on the course show page', function () {
        $response = $this->actingAs($this->learner)
            ->get("/courses/{$this->quiz->course_id}")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Courses/Show')
                ->has('course.quizzes', 1)
                ->has('course.quizzes.0', fn (Assert $quiz) => $quiz
                    ->hasAll(['id', 'title', 'pass_mark_percentage', 'questions_count'])
                    ->missing('questions')
                )
            );

        $encoded = json_encode($this->inertiaProps($response));

        expect($encoded)
            ->not->toContain('is_correct')
            ->not->toContain('Secret explanation')
            ->not->toContain('"answers"');
    });

    test('a learner cannot reach the edit page', function () {
        $this->actingAs($this->learner)
            ->get("/quizzes/{$this->quiz->id}/edit")
            ->assertStatus(403);
    });

    test('a learner cannot update a quiz', function () {
        $this->actingAs($this->learner)
            ->putJson("/quizzes/{$this->quiz->id}", $this->quizPayload($this->quiz->course, [
                ['question_id' => $this->quiz->questions()->first()->id, 'points' => null],
            ]))
            ->assertStatus(403);
    });

    test('the quiz index never includes question or answer data for a learner', function () {
        $response = $this->actingAs($this->learner)
            ->get('/quizzes')
            ->assertStatus(200);

        $encoded = json_encode($this->inertiaProps($response));

        expect($encoded)
            ->not->toContain('is_correct')
            ->not->toContain('Secret explanation');
    });
});

describe('editor view', function () {
    test('an editor receives is_correct and explanation on show', function () {
        $response = $this->actingAs($this->editor)
            ->get("/quizzes/{$this->quiz->id}")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Quizzes/Show')
                ->where('canEdit', true)
                ->has('quiz.questions.0.explanation')
                ->has('quiz.questions.0.answers.0.is_correct')
            );

        expect(json_encode($this->inertiaProps($response)))
            ->toContain('Secret explanation');
    });

    test('an editor can reach the edit page', function () {
        $this->actingAs($this->editor)
            ->get("/quizzes/{$this->quiz->id}/edit")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Quizzes/Edit')
                ->has('quiz.questions', 2)
            );
    });
});

describe('visibility for learners', function () {
    test('a learner cannot see a draft quiz', function () {
        $draft = $this->quizFor($this->editor, ['published_at' => null]);

        $this->actingAs($this->learner)
            ->get("/quizzes/{$draft->id}")
            ->assertStatus(404);
    });

    test('a learner cannot see a quiz scheduled for the future', function () {
        $scheduled = $this->quizFor($this->editor, ['published_at' => now()->addWeek()]);

        $this->actingAs($this->learner)
            ->get("/quizzes/{$scheduled->id}")
            ->assertStatus(404);
    });

    test('a learner cannot see a quiz in a draft course', function () {
        $draftCourse = $this->courseFor($this->editor, ['published_at' => null]);
        $quiz = $this->quizFor($this->editor, ['course' => $draftCourse]);

        $this->actingAs($this->learner)
            ->get("/quizzes/{$quiz->id}")
            ->assertStatus(404);
    });

    test('a learner cannot see a trashed quiz', function () {
        $trashed = $this->quizFor($this->editor);
        $trashed->delete();

        $this->actingAs($this->learner)
            ->get("/quizzes/{$trashed->id}")
            ->assertStatus(404);
    });

    test('draft quizzes are hidden from the learner course page', function () {
        $this->quizFor($this->editor, [
            'course' => $this->quiz->course,
            'title' => 'Hidden Draft',
            'published_at' => null,
        ]);

        $response = $this->actingAs($this->learner)
            ->get("/courses/{$this->quiz->course_id}")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Courses/Show')
                ->has('course.quizzes', 1)
            );

        expect(json_encode($this->inertiaProps($response)))
            ->not->toContain('Hidden Draft');
    });

    test('an editor sees draft quizzes on the course page', function () {
        $this->quizFor($this->editor, [
            'course' => $this->quiz->course,
            'title' => 'Visible Draft',
            'published_at' => null,
        ]);

        $this->actingAs($this->editor)
            ->get("/courses/{$this->quiz->course_id}")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Courses/Show')
                ->has('course.quizzes', 2)
            );
    });
});

describe('cross organisation leakage', function () {
    test('a quiz from organisation B returns 404 for a user in organisation A', function () {
        $foreign = $this->foreignQuiz();

        $this->actingAs($this->learner)
            ->get("/quizzes/{$foreign->id}")
            ->assertStatus(404);
    });
});
