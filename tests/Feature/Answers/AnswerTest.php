<?php

declare(strict_types=1);

use App\Enums\QuestionType;
use App\Models\Answer;
use App\Models\Question;
use App\Models\User;
use App\Models\Permission;

function answerUser(array $permissions = []): User
{
    $user = User::factory()->create();

    foreach ($permissions as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }

    $user->givePermissionTo($permissions);

    return $user;
}

it('forbids users without the view any permission', function () {
    $this->actingAs(answerUser())->get(route('answers.index'))->assertForbidden();
});

it('creates an answer and records the creator', function () {
    $user = answerUser(['create answer', 'edit question']);
    $question = Question::factory()->create();

    $this->actingAs($user)
        ->post(route('answers.store'), ['question_id' => $question->id, 'answer' => 'SoftDeletes'])
        ->assertRedirect();

    $this->assertDatabaseHas('answers', ['question_id' => $question->id, 'created_by' => $user->id]);
});

it('rejects an answer for a question the user cannot update', function () {
    $user = answerUser(['create answer']);
    $question = Question::factory()->create();

    $this->actingAs($user)
        ->post(route('answers.store'), ['question_id' => $question->id, 'answer' => 'SoftDeletes'])
        ->assertForbidden();
});

it('stops a single choice question having two correct answers', function () {
    $user = answerUser(['create answer', 'view correct answer', 'edit question']);
    $question = Question::factory()->create(['type' => QuestionType::SingleChoice]);
    Answer::factory()->create(['question_id' => $question->id, 'is_correct' => true]);

    $this->actingAs($user)
        ->post(route('answers.store'), [
            'question_id' => $question->id,
            'answer' => 'Second correct',
            'is_correct' => true,
        ])
        ->assertSessionHasErrors('is_correct');

    expect(Answer::where('question_id', $question->id)->count())->toBe(1);
});

it('stops a true or false question having a third answer', function () {
    $user = answerUser(['create answer', 'edit question']);
    $question = Question::factory()->create(['type' => QuestionType::TrueFalse]);
    Answer::factory()->count(2)->create(['question_id' => $question->id]);

    $this->actingAs($user)
        ->post(route('answers.store'), ['question_id' => $question->id, 'answer' => 'Maybe'])
        ->assertSessionHasErrors('is_correct');
});

it('ignores is_correct from users without view correct answer', function () {
    $user = answerUser(['create answer', 'edit question']);
    $question = Question::factory()->create();

    $this->actingAs($user)->post(route('answers.store'), [
        'question_id' => $question->id,
        'answer' => 'Sneaky',
        'is_correct' => true,
    ]);

    expect(Answer::where('answer', 'Sneaky')->first()->is_correct)->toBeFalse();
});

it('hides the answer key from users without view correct answer', function () {
    $user = answerUser(['view answer']);
    $answer = Answer::factory()->create(['is_correct' => true, 'feedback' => 'Secret']);

    $this->actingAs($user)
        ->get(route('answers.show', $answer))
        ->assertInertia(fn ($page) => $page
            ->component('Answers/Show')
            ->missing('answer.is_correct')
            ->missing('answer.feedback'));
});

it('ignores audit columns sent in the request', function () {
    $user = answerUser(['create answer', 'edit question']);
    $other = User::factory()->create();
    $question = Question::factory()->create();

    $this->actingAs($user)->post(route('answers.store'), [
        'question_id' => $question->id,
        'answer' => 'Audit test',
        'created_by' => $other->id,
    ]);

    expect(Answer::where('answer', 'Audit test')->first()->created_by)->toBe($user->id);
});

it('soft deletes and restores with audit columns', function () {
    $user = answerUser(['delete answer', 'restore answer', 'view answer']);
    $answer = Answer::factory()->create();

    $this->actingAs($user)->delete(route('answers.destroy', $answer))->assertRedirect();
    expect(Answer::withTrashed()->find($answer->id)->deleted_by)->toBe($user->id);

    $this->actingAs($user)->post(route('answers.restore', $answer->id))->assertRedirect();
    expect(Answer::find($answer->id)->restored_by)->toBe($user->id);
});

it('blocks exporting the answer key without view correct answer', function () {
    $user = answerUser(['export answer']);

    $this->actingAs($user)->get(route('answers.export'))->assertForbidden();
});

it('prevents a restore bringing a single choice question over its limit', function () {
    $user = answerUser(['restore answer']);
    $question = Question::factory()->create(['type' => QuestionType::SingleChoice]);
    $deleted = Answer::factory()->create(['question_id' => $question->id, 'is_correct' => true]);
    $deleted->delete();
    Answer::factory()->create(['question_id' => $question->id, 'is_correct' => true]);

    $this->actingAs($user)
        ->post(route('answers.restore', $deleted->id))
        ->assertSessionHasErrors('is_correct');

    expect(Answer::withTrashed()->find($deleted->id)->trashed())->toBeTrue();
});