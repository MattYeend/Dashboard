<?php

declare(strict_types=1);

use App\Enums\QuestionType;
use App\Models\Answer;
use App\Models\Log;
use App\Models\Question;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\CreatesUsers;

uses(
    LazilyRefreshDatabase::class,
    CreatesUsers::class,
);

describe('index', function () {
    test('authenticated user with permission can list answers', function () {
        $superAdmin = $this->superAdminUser();

        Answer::factory()->count(3)->create();

        $this->actingAs($superAdmin)
            ->get(route('answers.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Answers/Index')
                ->has('answers.data', 3)
                ->where('permissions_meta.can_view_correct', true)
            );
    });

    test('unauthenticated user cannot list answers', function () {
        $this->get(route('answers.index'))
            ->assertRedirect('/login');
    });

    test('user without permission cannot list answers', function () {
        $user = $this->userWithNoPermissions();

        $this->actingAs($user)
            ->get(route('answers.index'))
            ->assertForbidden();
    });

    test('a normal user can list answers but never receives the answer key', function () {
        $user = $this->normalUser();

        Answer::factory()->count(2)->create([
            'is_correct' => true,
            'feedback' => 'Secret feedback',
        ]);

        $this->actingAs($user)
            ->get(route('answers.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Answers/Index')
                ->has('answers.data', 2)
                ->where('permissions_meta.can_view_correct', false)
                ->missing('answers.data.0.is_correct')
                ->missing('answers.data.0.feedback')
            );
    });

    test('the is_correct filter is ignored for users who cannot view correct answers', function () {
        $user = $this->normalUser();
        $question = Question::factory()->create(['type' => QuestionType::MultipleChoice]);

        Answer::factory()->create(['question_id' => $question->id, 'is_correct' => true]);
        Answer::factory()->create(['question_id' => $question->id, 'is_correct' => false]);

        $this->actingAs($user)
            ->get(route('answers.index', ['is_correct' => 1]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('answers.data', 2)
            );
    });

    test('the is_correct filter works for users who can view correct answers', function () {
        $admin = $this->adminUser();
        $question = Question::factory()->create(['type' => QuestionType::MultipleChoice]);

        Answer::factory()->create(['question_id' => $question->id, 'is_correct' => true]);
        Answer::factory()->create(['question_id' => $question->id, 'is_correct' => false]);

        $this->actingAs($admin)
            ->get(route('answers.index', ['is_correct' => 1]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('answers.data', 1)
            );
    });

    test('answers can be filtered by question', function () {
        $admin = $this->adminUser();
        $question = Question::factory()->create();

        Answer::factory()->count(2)->create(['question_id' => $question->id]);
        Answer::factory()->create();

        $this->actingAs($admin)
            ->get(route('answers.index', ['question_id' => $question->id]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('answers.data', 2)
            );
    });

    test('index rejects sorting by the correctness column', function () {
        $admin = $this->adminUser();

        $this->actingAs($admin)
            ->get(route('answers.index', ['sort_by' => 'is_correct']))
            ->assertSessionHasErrors('sort_by');
    });
});

describe('create', function () {
    test('authenticated user with permission can view create form', function () {
        $admin = $this->adminUser();

        $this->actingAs($admin)
            ->get(route('answers.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Answers/Create')
                ->has('question_options')
            );
    });

    test('the create form preselects a question from the query string', function () {
        $admin = $this->adminUser();
        $question = Question::factory()->create();

        $this->actingAs($admin)
            ->get(route('answers.create', ['question_id' => $question->id]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('default_question_id', $question->id)
            );
    });

    test('unauthenticated user cannot view create form', function () {
        $this->get(route('answers.create'))
            ->assertRedirect('/login');
    });

    test('user without permission cannot view create form', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->get(route('answers.create'))
            ->assertForbidden();
    });
});

describe('store', function () {
    test('authenticated user with permission can create an answer', function () {
        $admin = $this->adminUser();
        $question = Question::factory()->create(['type' => QuestionType::MultipleChoice]);

        $this->actingAs($admin)
            ->postJson(route('answers.store'), [
                'question_id' => $question->id,
                'answer' => 'hasMany',
                'is_correct' => true,
                'feedback' => 'Defines a one to many relationship.',
                'position' => 1,
            ])
            ->assertCreated()
            ->assertJsonFragment(['answer' => 'hasMany']);

        $this->assertDatabaseHas('answers', [
            'question_id' => $question->id,
            'answer' => 'hasMany',
            'is_correct' => true,
            'position' => 1,
            'created_by' => $admin->id,
        ]);
    });

    test('a browser request redirects to the new answer', function () {
        $admin = $this->adminUser();
        $question = Question::factory()->create(['type' => QuestionType::MultipleChoice]);

        $this->actingAs($admin)
            ->post(route('answers.store'), [
                'question_id' => $question->id,
                'answer' => 'morphTo',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('answers', ['answer' => 'morphTo']);
    });

    test('unauthenticated user cannot create an answer', function () {
        $question = Question::factory()->create();

        $this->postJson(route('answers.store'), [
            'question_id' => $question->id,
            'answer' => 'Blocked',
        ])->assertUnauthorized();

        $this->assertDatabaseCount('answers', 0);
    });

    test('user without permission cannot create an answer', function () {
        $user = $this->normalUser();
        $question = Question::factory()->create();

        $this->actingAs($user)
            ->postJson(route('answers.store'), [
                'question_id' => $question->id,
                'answer' => 'Blocked',
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('answers', 0);
    });

    test('user who cannot update the parent question cannot add answers to it', function () {
        $user = $this->normalUser();
        $user->givePermissionTo('create answer');

        $question = Question::factory()->create();

        $this->actingAs($user)
            ->postJson(route('answers.store'), [
                'question_id' => $question->id,
                'answer' => 'Blocked',
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('answers', 0);
    });

    test('store fails validation when answer is missing', function () {
        $admin = $this->adminUser();
        $question = Question::factory()->create();

        $this->actingAs($admin)
            ->postJson(route('answers.store'), [
                'question_id' => $question->id,
                'answer' => '',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['answer']);
    });

    test('store fails validation when answer exceeds 2000 characters', function () {
        $admin = $this->adminUser();
        $question = Question::factory()->create();

        $this->actingAs($admin)
            ->postJson(route('answers.store'), [
                'question_id' => $question->id,
                'answer' => str_repeat('a', 2001),
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['answer']);
    });

    test('store fails validation when question_id is missing', function () {
        $admin = $this->adminUser();

        $this->actingAs($admin)
            ->postJson(route('answers.store'), [
                'answer' => 'No question',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['question_id']);
    });

    test('store fails validation when question_id does not exist', function () {
        $admin = $this->adminUser();

        $this->actingAs($admin)
            ->postJson(route('answers.store'), [
                'question_id' => 999999,
                'answer' => 'Orphan',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['question_id']);
    });

    test('store fails validation when question is soft deleted', function () {
        $admin = $this->adminUser();
        $question = Question::factory()->create();
        $question->delete();

        $this->actingAs($admin)
            ->postJson(route('answers.store'), [
                'question_id' => $question->id,
                'answer' => 'Too late',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['question_id']);
    });

    test('store fails validation when position is out of range', function () {
        $admin = $this->adminUser();
        $question = Question::factory()->create();

        $this->actingAs($admin)
            ->postJson(route('answers.store'), [
                'question_id' => $question->id,
                'answer' => 'Far away',
                'position' => 70000,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['position']);
    });

    test('store places a new answer after the existing ones when no position is given', function () {
        $admin = $this->adminUser();
        $question = Question::factory()->create(['type' => QuestionType::MultipleChoice]);

        Answer::factory()->create(['question_id' => $question->id, 'position' => 3]);

        $this->actingAs($admin)
            ->postJson(route('answers.store'), [
                'question_id' => $question->id,
                'answer' => 'Next in line',
            ])
            ->assertCreated();

        $this->assertDatabaseHas('answers', [
            'answer' => 'Next in line',
            'position' => 4,
        ]);
    });

    test('store ignores audit columns sent in the request', function () {
        $admin = $this->adminUser();
        $other = $this->adminUser();
        $question = Question::factory()->create();

        $this->actingAs($admin)
            ->postJson(route('answers.store'), [
                'question_id' => $question->id,
                'answer' => 'Audit test',
                'created_by' => $other->id,
            ])
            ->assertCreated();

        $this->assertDatabaseHas('answers', [
            'answer' => 'Audit test',
            'created_by' => $admin->id,
        ]);
    });

    test('store ignores is_correct from users who cannot view correct answers', function () {
        $user = $this->normalUser();
        $user->givePermissionTo(['create answer', 'edit question']);

        $question = Question::factory()->create(['type' => QuestionType::MultipleChoice]);

        $this->actingAs($user)
            ->postJson(route('answers.store'), [
                'question_id' => $question->id,
                'answer' => 'Sneaky',
                'is_correct' => true,
                'feedback' => 'Sneaky feedback',
            ])
            ->assertCreated();

        $this->assertDatabaseHas('answers', [
            'answer' => 'Sneaky',
            'is_correct' => false,
            'feedback' => null,
        ]);
    });

    test('store response does not expose the answer key to users who cannot view it', function () {
        $user = $this->normalUser();
        $user->givePermissionTo(['create answer', 'edit question']);

        $question = Question::factory()->create(['type' => QuestionType::MultipleChoice]);

        $this->actingAs($user)
            ->postJson(route('answers.store'), [
                'question_id' => $question->id,
                'answer' => 'Hidden key',
            ])
            ->assertCreated()
            ->assertJsonMissingPath('is_correct')
            ->assertJsonMissingPath('feedback');
    });

    test('logs answer creation with actor id', function () {
        $admin = $this->adminUser();
        $question = Question::factory()->create();

        $this->actingAs($admin)
            ->postJson(route('answers.store'), [
                'question_id' => $question->id,
                'answer' => 'Logged answer',
            ])
            ->assertCreated();

        $log = Log::query()
            ->where('action_id', Log::ACTION_CREATE_ANSWER)
            ->where('logged_in_user_id', $admin->id)
            ->first();

        expect($log)->not->toBeNull()
            ->and($log->data)->toHaveKeys(['after']);
    });
});

describe('answer set rules', function () {
    test('a single choice question cannot have two correct answers', function () {
        $admin = $this->adminUser();
        $question = Question::factory()->create(['type' => QuestionType::SingleChoice]);

        Answer::factory()->create(['question_id' => $question->id, 'is_correct' => true]);

        $this->actingAs($admin)
            ->postJson(route('answers.store'), [
                'question_id' => $question->id,
                'answer' => 'Second correct',
                'is_correct' => true,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['is_correct']);

        expect(Answer::where('question_id', $question->id)->count())->toBe(1);
    });

    test('a single choice question can have many incorrect answers', function () {
        $admin = $this->adminUser();
        $question = Question::factory()->create(['type' => QuestionType::SingleChoice]);

        Answer::factory()->create(['question_id' => $question->id, 'is_correct' => true]);

        $this->actingAs($admin)
            ->postJson(route('answers.store'), [
                'question_id' => $question->id,
                'answer' => 'Wrong one',
                'is_correct' => false,
            ])
            ->assertCreated();

        expect(Answer::where('question_id', $question->id)->count())->toBe(2);
    });

    test('a multiple choice question can have several correct answers', function () {
        $admin = $this->adminUser();
        $question = Question::factory()->create(['type' => QuestionType::MultipleChoice]);

        Answer::factory()->create(['question_id' => $question->id, 'is_correct' => true]);

        $this->actingAs($admin)
            ->postJson(route('answers.store'), [
                'question_id' => $question->id,
                'answer' => 'Also correct',
                'is_correct' => true,
            ])
            ->assertCreated();

        expect(Answer::where('question_id', $question->id)->where('is_correct', true)->count())->toBe(2);
    });

    test('a true or false question cannot have a third answer', function () {
        $admin = $this->adminUser();
        $question = Question::factory()->create(['type' => QuestionType::TrueFalse]);

        Answer::factory()->count(2)->create(['question_id' => $question->id]);

        $this->actingAs($admin)
            ->postJson(route('answers.store'), [
                'question_id' => $question->id,
                'answer' => 'Maybe',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['is_correct']);

        expect(Answer::where('question_id', $question->id)->count())->toBe(2);
    });

    test('a short text question accepts several correct answers', function () {
        $admin = $this->adminUser();
        $question = Question::factory()->create(['type' => QuestionType::ShortText]);

        foreach (['SoftDeletes', 'Illuminate\\Database\\Eloquent\\SoftDeletes'] as $accepted) {
            $this->actingAs($admin)
                ->postJson(route('answers.store'), [
                    'question_id' => $question->id,
                    'answer' => $accepted,
                    'is_correct' => true,
                ])
                ->assertCreated();
        }

        expect(Answer::where('question_id', $question->id)->count())->toBe(2);
    });

    test('updating an answer cannot give a single choice question two correct answers', function () {
        $admin = $this->adminUser();
        $question = Question::factory()->create(['type' => QuestionType::SingleChoice]);

        Answer::factory()->create(['question_id' => $question->id, 'is_correct' => true]);
        $other = Answer::factory()->create(['question_id' => $question->id, 'is_correct' => false]);

        $this->actingAs($admin)
            ->putJson(route('answers.update', $other), ['is_correct' => true])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['is_correct']);

        $this->assertDatabaseHas('answers', ['id' => $other->id, 'is_correct' => false]);
    });
});

describe('show', function () {
    test('authenticated user with permission can view an answer with the key', function () {
        $admin = $this->adminUser();
        $answer = Answer::factory()->create(['is_correct' => true, 'feedback' => 'Because.']);

        $this->actingAs($admin)
            ->get(route('answers.show', $answer))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Answers/Show')
                ->where('answer.is_correct', true)
                ->where('answer.feedback', 'Because.')
                ->has('abilities')
            );
    });

    test('unauthenticated user cannot view an answer', function () {
        $answer = Answer::factory()->create();

        $this->get(route('answers.show', $answer))
            ->assertRedirect('/login');
    });

    test('user without permission cannot view an answer', function () {
        $user = $this->userWithNoPermissions();
        $answer = Answer::factory()->create();

        $this->actingAs($user)
            ->get(route('answers.show', $answer))
            ->assertForbidden();
    });

    test('a normal user can view an answer but never receives the answer key', function () {
        $user = $this->normalUser();
        $answer = Answer::factory()->create(['is_correct' => true, 'feedback' => 'Secret']);

        $this->actingAs($user)
            ->get(route('answers.show', $answer))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Answers/Show')
                ->missing('answer.is_correct')
                ->missing('answer.feedback')
            );
    });

    test('user cannot view an answer created by a super admin', function () {
        $superAdmin = $this->superAdminUser();
        $user = $this->normalUser();

        $answer = Answer::factory()->create(['created_by' => $superAdmin->id]);

        $this->actingAs($user)
            ->get(route('answers.show', $answer))
            ->assertForbidden();
    });

    test('show returns 404 for a non-existent answer', function () {
        $admin = $this->adminUser();

        $this->actingAs($admin)
            ->get(route('answers.show', 99999))
            ->assertNotFound();
    });

    test('show returns 404 for a soft-deleted answer', function () {
        $admin = $this->adminUser();
        $answer = Answer::factory()->deleted()->create();

        $this->actingAs($admin)
            ->get(route('answers.show', $answer->id))
            ->assertNotFound();
    });
});

describe('edit', function () {
    test('authenticated user with permission can view edit form', function () {
        $admin = $this->adminUser();
        $answer = Answer::factory()->create();

        $this->actingAs($admin)
            ->get(route('answers.edit', $answer))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Answers/Edit')
                ->has('answer')
            );
    });

    test('unauthenticated user cannot view edit form', function () {
        $answer = Answer::factory()->create();

        $this->get(route('answers.edit', $answer))
            ->assertRedirect('/login');
    });

    test('user without permission cannot view edit form', function () {
        $user = $this->normalUser();
        $answer = Answer::factory()->create();

        $this->actingAs($user)
            ->get(route('answers.edit', $answer))
            ->assertForbidden();
    });
});

describe('update', function () {
    test('authenticated user with permission can update an answer', function () {
        $admin = $this->adminUser();
        $answer = Answer::factory()->create(['answer' => 'Old answer']);

        $this->actingAs($admin)
            ->putJson(route('answers.update', $answer), ['answer' => 'New answer'])
            ->assertOk()
            ->assertJsonFragment(['answer' => 'New answer']);

        $this->assertDatabaseHas('answers', [
            'id' => $answer->id,
            'answer' => 'New answer',
            'updated_by' => $admin->id,
        ]);
    });

    test('patch verb also updates an answer', function () {
        $admin = $this->adminUser();
        $answer = Answer::factory()->create(['feedback' => 'Old feedback']);

        $this->actingAs($admin)
            ->patchJson(route('answers.update', $answer), ['feedback' => 'New feedback'])
            ->assertOk()
            ->assertJsonFragment(['feedback' => 'New feedback']);
    });

    test('unauthenticated user cannot update an answer', function () {
        $answer = Answer::factory()->create();

        $this->putJson(route('answers.update', $answer), ['answer' => 'Blocked'])
            ->assertUnauthorized();
    });

    test('user without permission cannot update an answer', function () {
        $user = $this->normalUser();
        $answer = Answer::factory()->create();

        $this->actingAs($user)
            ->putJson(route('answers.update', $answer), ['answer' => 'Blocked'])
            ->assertForbidden();
    });

    test('update fails validation when answer is empty', function () {
        $admin = $this->adminUser();
        $answer = Answer::factory()->create();

        $this->actingAs($admin)
            ->putJson(route('answers.update', $answer), ['answer' => ''])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['answer']);
    });

    test('nullable fields can be cleared by passing null on update', function () {
        $admin = $this->adminUser();
        $answer = Answer::factory()->create(['feedback' => 'Some feedback.']);

        $this->actingAs($admin)
            ->putJson(route('answers.update', $answer), ['feedback' => null])
            ->assertOk();

        $this->assertDatabaseHas('answers', [
            'id' => $answer->id,
            'feedback' => null,
        ]);
    });

    test('omitted fields are not cleared on update', function () {
        $admin = $this->adminUser();
        $answer = Answer::factory()->create([
            'answer' => 'Original answer',
            'feedback' => 'Original feedback.',
            'position' => 5,
        ]);

        $this->actingAs($admin)
            ->putJson(route('answers.update', $answer), ['answer' => 'Updated answer'])
            ->assertOk();

        $this->assertDatabaseHas('answers', [
            'id' => $answer->id,
            'answer' => 'Updated answer',
            'feedback' => 'Original feedback.',
            'position' => 5,
        ]);
    });

    test('the parent question cannot be changed on update', function () {
        $admin = $this->adminUser();
        $original = Question::factory()->create();
        $other = Question::factory()->create();

        $answer = Answer::factory()->create(['question_id' => $original->id]);

        $this->actingAs($admin)
            ->putJson(route('answers.update', $answer), [
                'answer' => 'Moved?',
                'question_id' => $other->id,
            ])
            ->assertOk();

        $this->assertDatabaseHas('answers', [
            'id' => $answer->id,
            'question_id' => $original->id,
        ]);
    });

    test('update ignores is_correct from users who cannot view correct answers', function () {
        $user = $this->normalUser();
        $user->givePermissionTo('edit answer');

        $answer = Answer::factory()->create(['is_correct' => false]);

        $this->actingAs($user)
            ->putJson(route('answers.update', $answer), [
                'answer' => 'Edited',
                'is_correct' => true,
            ])
            ->assertOk();

        $this->assertDatabaseHas('answers', [
            'id' => $answer->id,
            'answer' => 'Edited',
            'is_correct' => false,
        ]);
    });

    test('update response does not expose the answer key to users who cannot view it', function () {
        $user = $this->normalUser();
        $user->givePermissionTo('edit answer');

        $answer = Answer::factory()->create(['is_correct' => true, 'feedback' => 'Secret']);

        $this->actingAs($user)
            ->putJson(route('answers.update', $answer), ['answer' => 'Edited'])
            ->assertOk()
            ->assertJsonMissingPath('is_correct')
            ->assertJsonMissingPath('feedback');
    });

    test('logs answer updates with actor id', function () {
        $admin = $this->adminUser();
        $answer = Answer::factory()->create(['answer' => 'Old']);

        $this->actingAs($admin)
            ->putJson(route('answers.update', $answer), ['answer' => 'New'])
            ->assertOk();

        $log = Log::query()
            ->where('action_id', Log::ACTION_UPDATE_ANSWER)
            ->where('logged_in_user_id', $admin->id)
            ->first();

        expect($log)->not->toBeNull()
            ->and($log->data)->toHaveKeys(['before', 'after']);
    });
});

describe('destroy', function () {
    test('authenticated user with permission can soft delete an answer', function () {
        $admin = $this->adminUser();
        $answer = Answer::factory()->create();

        $this->actingAs($admin)
            ->deleteJson(route('answers.destroy', $answer))
            ->assertNoContent();

        $this->assertSoftDeleted('answers', [
            'id' => $answer->id,
            'deleted_by' => $admin->id,
        ]);
    });

    test('user without permission cannot soft delete an answer', function () {
        $user = $this->normalUser();
        $answer = Answer::factory()->create();

        $this->actingAs($user)
            ->deleteJson(route('answers.destroy', $answer))
            ->assertForbidden();

        $this->assertDatabaseHas('answers', ['id' => $answer->id, 'deleted_at' => null]);
    });

    test('an admin cannot delete an answer created by a super admin', function () {
        $superAdmin = $this->superAdminUser();
        $admin = $this->adminUser();

        $answer = Answer::factory()->create(['created_by' => $superAdmin->id]);

        $this->actingAs($admin)
            ->deleteJson(route('answers.destroy', $answer))
            ->assertForbidden();

        $this->assertDatabaseHas('answers', ['id' => $answer->id, 'deleted_at' => null]);
    });

    test('destroy returns 404 for a non-existent answer', function () {
        $admin = $this->adminUser();

        $this->actingAs($admin)
            ->deleteJson(route('answers.destroy', 99999))
            ->assertNotFound();
    });

    test('logs answer deletion with actor id', function () {
        $admin = $this->adminUser();
        $answer = Answer::factory()->create();

        $this->actingAs($admin)
            ->deleteJson(route('answers.destroy', $answer))
            ->assertNoContent();

        $log = Log::query()
            ->where('action_id', Log::ACTION_DELETE_ANSWER)
            ->where('logged_in_user_id', $admin->id)
            ->first();

        expect($log)->not->toBeNull()
            ->and($log->data)->toHaveKeys(['before']);
    });
});

describe('restore', function () {
    test('authenticated user with permission can restore a soft-deleted answer', function () {
        $admin = $this->adminUser();
        $answer = Answer::factory()->deleted()->create();

        $this->actingAs($admin)
            ->postJson(route('answers.restore', $answer->id))
            ->assertNoContent();

        $this->assertDatabaseHas('answers', [
            'id' => $answer->id,
            'deleted_at' => null,
            'restored_by' => $admin->id,
        ]);
    });

    test('user without permission cannot restore an answer', function () {
        $user = $this->normalUser();
        $answer = Answer::factory()->deleted()->create();

        $this->actingAs($user)
            ->postJson(route('answers.restore', $answer->id))
            ->assertForbidden();
    });

    test('restore returns 404 for an answer that is not soft-deleted', function () {
        $admin = $this->adminUser();
        $answer = Answer::factory()->create();

        $this->actingAs($admin)
            ->postJson(route('answers.restore', $answer->id))
            ->assertNotFound();
    });

    test('restore cannot bring a single choice question over its limit', function () {
        $admin = $this->adminUser();
        $question = Question::factory()->create(['type' => QuestionType::SingleChoice]);

        $deleted = Answer::factory()->deleted()->create([
            'question_id' => $question->id,
            'is_correct' => true,
        ]);
        Answer::factory()->create(['question_id' => $question->id, 'is_correct' => true]);

        $this->actingAs($admin)
            ->postJson(route('answers.restore', $deleted->id))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['is_correct']);

        $this->assertSoftDeleted('answers', ['id' => $deleted->id]);
    });

    test('logs answer restoration with actor id', function () {
        $admin = $this->adminUser();
        $answer = Answer::factory()->deleted()->create();

        $this->actingAs($admin)
            ->postJson(route('answers.restore', $answer->id))
            ->assertNoContent();

        $log = Log::query()
            ->where('action_id', Log::ACTION_RESTORE_ANSWER)
            ->where('logged_in_user_id', $admin->id)
            ->first();

        expect($log)->not->toBeNull();
    });
});

describe('force delete', function () {
    test('authenticated user with permission can force delete an answer', function () {
        $superAdmin = $this->superAdminUser();
        $answer = Answer::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->deleteJson(route('answers.force-delete', $answer->id))
            ->assertNoContent();

        $this->assertDatabaseMissing('answers', ['id' => $answer->id]);
    });

    test('an admin cannot force delete an answer', function () {
        $admin = $this->adminUser();
        $answer = Answer::factory()->deleted()->create();

        $this->actingAs($admin)
            ->deleteJson(route('answers.force-delete', $answer->id))
            ->assertForbidden();

        $this->assertSoftDeleted('answers', ['id' => $answer->id]);
    });

    test('user without permission cannot force delete an answer', function () {
        $user = $this->normalUser();
        $answer = Answer::factory()->deleted()->create();

        $this->actingAs($user)
            ->deleteJson(route('answers.force-delete', $answer->id))
            ->assertForbidden();
    });

    test('force delete returns 404 for an answer that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();
        $answer = Answer::factory()->create();

        $this->actingAs($superAdmin)
            ->deleteJson(route('answers.force-delete', $answer->id))
            ->assertNotFound();
    });
});

describe('bulk delete', function () {
    test('authenticated user with permission can bulk soft delete answers', function () {
        $admin = $this->adminUser();

        $answers = Answer::factory()->count(3)->create();
        $ids = $answers->pluck('id')->all();

        $this->actingAs($admin)
            ->postJson(route('answers.bulk.delete'), ['ids' => $ids])
            ->assertOk()
            ->assertJson([
                'deleted' => $ids,
                'skipped' => [],
            ]);

        foreach ($ids as $id) {
            $this->assertSoftDeleted('answers', ['id' => $id]);
        }
    });

    test('bulk delete fails validation with empty ids array', function () {
        $admin = $this->adminUser();

        $this->actingAs($admin)
            ->postJson(route('answers.bulk.delete'), ['ids' => []])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk delete fails validation with more than 100 ids', function () {
        $admin = $this->adminUser();

        $this->actingAs($admin)
            ->postJson(route('answers.bulk.delete'), ['ids' => range(1, 101)])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk delete skips non-existent ids', function () {
        $admin = $this->adminUser();

        $this->actingAs($admin)
            ->postJson(route('answers.bulk.delete'), ['ids' => [99999]])
            ->assertOk()
            ->assertJson([
                'deleted' => [],
                'skipped' => [99999],
            ]);
    });

    test('user without permission cannot bulk delete answers', function () {
        $user = $this->normalUser();
        $answers = Answer::factory()->count(2)->create();

        $this->actingAs($user)
            ->postJson(route('answers.bulk.delete'), [
                'ids' => $answers->pluck('id')->all(),
            ])
            ->assertForbidden();
    });
});

describe('bulk restore', function () {
    test('authenticated user with permission can bulk restore answers', function () {
        $admin = $this->adminUser();

        $answers = Answer::factory()->count(3)->deleted()->create();
        $ids = $answers->pluck('id')->all();

        $this->actingAs($admin)
            ->postJson(route('answers.bulk.restore'), ['ids' => $ids])
            ->assertOk()
            ->assertJson([
                'restored' => $ids,
                'skipped' => [],
            ]);

        foreach ($ids as $id) {
            $this->assertDatabaseHas('answers', [
                'id' => $id,
                'deleted_at' => null,
            ]);
        }
    });

    test('bulk restore fails validation with empty ids array', function () {
        $admin = $this->adminUser();

        $this->actingAs($admin)
            ->postJson(route('answers.bulk.restore'), ['ids' => []])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk restore skips non-existent ids', function () {
        $admin = $this->adminUser();

        $this->actingAs($admin)
            ->postJson(route('answers.bulk.restore'), ['ids' => [99999]])
            ->assertOk()
            ->assertJson([
                'restored' => [],
                'skipped' => [99999],
            ]);
    });

    test('user without permission cannot bulk restore answers', function () {
        $user = $this->normalUser();
        $answers = Answer::factory()->count(2)->deleted()->create();

        $this->actingAs($user)
            ->postJson(route('answers.bulk.restore'), [
                'ids' => $answers->pluck('id')->all(),
            ])
            ->assertForbidden();
    });
});

describe('export', function () {
    test('authenticated user with permission can export answers', function () {
        $admin = $this->adminUser();

        Answer::factory()->count(2)->create();

        $this->actingAs($admin)
            ->get(route('answers.export'))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    });

    test('unauthenticated user cannot export answers', function () {
        $this->get(route('answers.export'))
            ->assertRedirect('/login');
    });

    test('user without permission cannot export answers', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->get(route('answers.export'))
            ->assertForbidden();
    });

    test('a user who can export but cannot view correct answers is blocked', function () {
        $user = $this->normalUser();
        $user->givePermissionTo('export answer');

        $this->actingAs($user)
            ->get(route('answers.export'))
            ->assertForbidden();
    });

    test('export neutralises spreadsheet formula injection', function () {
        $admin = $this->adminUser();

        Answer::factory()->create(['answer' => '=1+1', 'feedback' => '@SUM(A1)']);

        $content = $this->actingAs($admin)
            ->get(route('answers.export'))
            ->assertOk()
            ->streamedContent();

        expect($content)
            ->toContain("'=1+1")
            ->toContain("'@SUM(A1)");
    });

    test('logs answer exports with actor id', function () {
        $admin = $this->adminUser();

        $this->actingAs($admin)
            ->get(route('answers.export'))
            ->assertOk()
            ->streamedContent();

        $log = Log::query()
            ->where('action_id', Log::ACTION_EXPORT_ANSWER)
            ->where('logged_in_user_id', $admin->id)
            ->first();

        expect($log)->not->toBeNull();
    });
});

describe('import', function () {
    test('authenticated user with permission can import answers', function () {
        $admin = $this->adminUser();
        $question = Question::factory()->create(['type' => QuestionType::MultipleChoice]);

        $file = UploadedFile::fake()->createWithContent(
            'answers.csv',
            "question_id,answer,is_correct,feedback\n{$question->id},hasMany,yes,Valid\n{$question->id},hasEverything,no,\n"
        );

        $this->actingAs($admin)
            ->postJson(route('answers.import'), ['file' => $file])
            ->assertOk()
            ->assertJson(['imported' => 2, 'skipped' => []]);

        $this->assertDatabaseHas('answers', [
            'question_id' => $question->id,
            'answer' => 'hasMany',
            'is_correct' => true,
            'created_by' => $admin->id,
        ]);
    });

    test('import skips rows that break the answer set rules', function () {
        $admin = $this->adminUser();
        $question = Question::factory()->create(['type' => QuestionType::SingleChoice]);

        $file = UploadedFile::fake()->createWithContent(
            'answers.csv',
            "question_id,answer,is_correct\n{$question->id},First,yes\n{$question->id},Second,yes\n"
        );

        $this->actingAs($admin)
            ->postJson(route('answers.import'), ['file' => $file])
            ->assertOk()
            ->assertJsonPath('imported', 1)
            ->assertJsonCount(1, 'skipped');

        expect(Answer::where('question_id', $question->id)->count())->toBe(1);
    });

    test('import skips rows for questions that do not exist', function () {
        $admin = $this->adminUser();

        $file = UploadedFile::fake()->createWithContent(
            'answers.csv',
            "question_id,answer\n999999,Orphan\n"
        );

        $this->actingAs($admin)
            ->postJson(route('answers.import'), ['file' => $file])
            ->assertOk()
            ->assertJsonPath('imported', 0)
            ->assertJsonCount(1, 'skipped');
    });

    test('import reports missing required columns', function () {
        $admin = $this->adminUser();

        $file = UploadedFile::fake()->createWithContent(
            'answers.csv',
            "answer\nNo question column\n"
        );

        $this->actingAs($admin)
            ->postJson(route('answers.import'), ['file' => $file])
            ->assertOk()
            ->assertJsonPath('imported', 0)
            ->assertJsonPath('skipped.0.row', 0);
    });

    test('import fails validation when no file is supplied', function () {
        $admin = $this->adminUser();

        $this->actingAs($admin)
            ->postJson(route('answers.import'), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['file']);
    });

    test('user without permission cannot import answers', function () {
        $user = $this->normalUser();

        $file = UploadedFile::fake()->createWithContent('answers.csv', "question_id,answer\n1,Blocked\n");

        $this->actingAs($user)
            ->postJson(route('answers.import'), ['file' => $file])
            ->assertForbidden();
    });

    test('a user who can import but cannot view correct answers is blocked', function () {
        $user = $this->normalUser();
        $user->givePermissionTo('import answer');

        $file = UploadedFile::fake()->createWithContent('answers.csv', "question_id,answer\n1,Blocked\n");

        $this->actingAs($user)
            ->postJson(route('answers.import'), ['file' => $file])
            ->assertForbidden();
    });
});

describe('soft delete scoping', function () {
    test('index does not return soft-deleted answers', function () {
        $admin = $this->adminUser();

        Answer::factory()->count(2)->create();
        Answer::factory()->deleted()->create();

        $this->actingAs($admin)
            ->get(route('answers.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Answers/Index')
                ->has('answers.data', 2)
            );
    });

    test('users who can restore can list soft-deleted answers', function () {
        $admin = $this->adminUser();

        Answer::factory()->create();
        Answer::factory()->deleted()->create();

        $this->actingAs($admin)
            ->get(route('answers.index', ['trashed' => 'only']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('answers.data', 1)
            );
    });

    test('users who cannot restore never see soft-deleted answers', function () {
        $user = $this->normalUser();

        Answer::factory()->create();
        Answer::factory()->deleted()->create();

        $this->actingAs($user)
            ->get(route('answers.index', ['trashed' => 'only']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('answers.data', 1)
            );
    });
});
