<?php

declare(strict_types=1);

use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\CreatesQuizzes;
use Tests\Concerns\CreatesUsers;

uses(
    LazilyRefreshDatabase::class,
    CreatesUsers::class,
    CreatesQuizzes::class,
);

describe('attachment rules', function () {
    test('a question from another organisation is rejected', function () {
        $admin = $this->adminUser();
        $course = $this->courseFor($admin);
        $foreign = $this->foreignQuestion();

        $this->actingAs($admin)
            ->postJson('/quizzes', $this->quizPayload($course, [
                ['question_id' => $foreign->id, 'points' => null],
            ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['questions.0.question_id']);

        $this->assertDatabaseMissing('quizzes', ['title' => 'Laravel Basics Quiz']);
    });

    test('a trashed question is rejected', function () {
        $admin = $this->adminUser();
        $course = $this->courseFor($admin);
        $question = $this->questionIn($this->testOrganisation->id);
        $question->delete();

        $this->actingAs($admin)
            ->postJson('/quizzes', $this->quizPayload($course, [
                ['question_id' => $question->id, 'points' => null],
            ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['questions.0.question_id']);
    });

    test('a duplicate question id is rejected', function () {
        $admin = $this->adminUser();
        $course = $this->courseFor($admin);
        $question = $this->questionIn($this->testOrganisation->id);

        $this->actingAs($admin)
            ->postJson('/quizzes', $this->quizPayload($course, [
                ['question_id' => $question->id, 'points' => null],
                ['question_id' => $question->id, 'points' => 2],
            ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['questions.0.question_id', 'questions.1.question_id']);
    });

    test('a question with no correct answer is rejected', function () {
        $admin = $this->adminUser();
        $course = $this->courseFor($admin);
        $question = $this->questionIn($this->testOrganisation->id, false);

        $this->actingAs($admin)
            ->postJson('/quizzes', $this->quizPayload($course, [
                ['question_id' => $question->id, 'points' => null],
            ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['questions.0.question_id']);
    });

    test('an empty question list is rejected', function () {
        $admin = $this->adminUser();
        $course = $this->courseFor($admin);

        $this->actingAs($admin)
            ->postJson('/quizzes', $this->quizPayload($course, []))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['questions']);
    });

    test('101 questions are rejected', function () {
        $admin = $this->adminUser();
        $course = $this->courseFor($admin);

        $questions = Question::factory()
            ->count(101)
            ->create(['organisation_id' => $this->testOrganisation->id])
            ->map(fn (Question $question) => ['question_id' => $question->id, 'points' => null])
            ->all();

        $this->actingAs($admin)
            ->postJson('/quizzes', $this->quizPayload($course, $questions))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['questions']);

        $this->assertDatabaseMissing('quizzes', ['title' => 'Laravel Basics Quiz']);
    });

    test('a non-array questions value is rejected', function () {
        $admin = $this->adminUser();
        $course = $this->courseFor($admin);

        $this->actingAs($admin)
            ->postJson('/quizzes', $this->quizPayload($course, [], ['questions' => 'not-an-array']))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['questions']);
    });
});

describe('points override', function () {
    test('points accept values from 1 to 100 and null', function () {
        $admin = $this->adminUser();
        $course = $this->courseFor($admin);

        $low = $this->questionIn($this->testOrganisation->id);
        $high = $this->questionIn($this->testOrganisation->id);
        $default = $this->questionIn($this->testOrganisation->id);

        $this->actingAs($admin)
            ->postJson('/quizzes', $this->quizPayload($course, [
                ['question_id' => $low->id, 'points' => 1],
                ['question_id' => $high->id, 'points' => 100],
                ['question_id' => $default->id, 'points' => null],
            ]))
            ->assertStatus(201);

        $quiz = Quiz::query()->where('title', 'Laravel Basics Quiz')->firstOrFail();

        $this->assertDatabaseHas('quiz_question', ['quiz_id' => $quiz->id, 'question_id' => $low->id, 'points' => 1]);
        $this->assertDatabaseHas('quiz_question', ['quiz_id' => $quiz->id, 'question_id' => $high->id, 'points' => 100]);
        $this->assertDatabaseHas('quiz_question', ['quiz_id' => $quiz->id, 'question_id' => $default->id, 'points' => null]);
    });

    test('points of 0 are rejected', function () {
        $admin = $this->adminUser();
        $course = $this->courseFor($admin);
        $question = $this->questionIn($this->testOrganisation->id);

        $this->actingAs($admin)
            ->postJson('/quizzes', $this->quizPayload($course, [
                ['question_id' => $question->id, 'points' => 0],
            ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['questions.0.points']);
    });

    test('points of 101 are rejected', function () {
        $admin = $this->adminUser();
        $course = $this->courseFor($admin);
        $question = $this->questionIn($this->testOrganisation->id);

        $this->actingAs($admin)
            ->postJson('/quizzes', $this->quizPayload($course, [
                ['question_id' => $question->id, 'points' => 101],
            ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['questions.0.points']);
    });

    test('effective_points uses the override, else the question default', function () {
        $admin = $this->adminUser();
        $quiz = $this->quizFor($admin);

        $overridden = $this->questionIn($this->testOrganisation->id, true, ['points' => 2]);
        $defaulted = $this->questionIn($this->testOrganisation->id, true, ['points' => 3]);

        $this->attachQuestion($quiz, $overridden, 1, 10);
        $this->attachQuestion($quiz, $defaulted, 2);

        $this->actingAs($admin)
            ->get("/quizzes/{$quiz->id}")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Quizzes/Show')
                ->where('quiz.questions.0.effective_points', 10)
                ->where('quiz.questions.1.effective_points', 3)
            );
    });
});

describe('question lifecycle', function () {
    test('soft deleting a question removes it from the learner view of the quiz', function () {
        $admin = $this->adminUser();
        $learner = $this->userWithPermissions(['view any quizzes', 'view quizzes']);

        $quiz = $this->quizFor($admin, [], 2);
        $removed = $quiz->questions()->orderBy('quiz_question.position')->first();
        $removed->delete();

        $this->actingAs($learner)
            ->get("/quizzes/{$quiz->id}")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Quizzes/Show')
                ->has('quiz.questions', 1)
                ->where('quiz.questions.0.id', fn ($id) => $id !== $removed->id)
            );
    });

    test('force deleting a question that is in a quiz is refused', function () {
        $admin = $this->superAdminUser();
        $quiz = $this->quizFor($admin, [], 1);
        $question = $quiz->questions()->first();
        $question->delete();

        $this->actingAs($admin)
            ->deleteJson("/questions/{$question->id}/force")
            ->assertStatus(422);

        $this->assertDatabaseHas('questions', ['id' => $question->id]);
        $this->assertDatabaseHas('quiz_question', [
            'quiz_id' => $quiz->id,
            'question_id' => $question->id,
        ]);
    });
});
