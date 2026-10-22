<?php

declare(strict_types=1);

use App\Models\Log;
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

afterEach(function () {
    Quiz::flushEventListeners();
    Quiz::clearBootedModels();
});

describe('index', function () {
    test('authenticated user with permission can list quizzes', function () {
        $superAdmin = $this->superAdminUser();

        $this->quizFor($superAdmin);
        $this->quizFor($superAdmin);

        $this->actingAs($superAdmin)
            ->get('/quizzes')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Quizzes/Index')
                ->has('quizzes')
            );
    });

    test('unauthenticated user cannot list quizzes', function () {
        $this->get('/quizzes')
            ->assertRedirect('/login');
    });

    test('user without permission cannot list quizzes', function () {
        $user = $this->userWithNoPermissions();

        $this->actingAs($user)
            ->get('/quizzes')
            ->assertStatus(403);
    });
});

describe('create', function () {
    test('authenticated user with permission can view create form', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/quizzes/create')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Quizzes/Create')
                ->has('courses')
                ->has('questionOptions')
            );
    });

    test('unauthenticated user cannot view create form', function () {
        $this->get('/quizzes/create')
            ->assertRedirect('/login');
    });

    test('user without permission cannot view create form', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->get('/quizzes/create')
            ->assertStatus(403);
    });
});

describe('store', function () {
    test('authenticated user with permission can create a quiz with questions', function () {
        $superAdmin = $this->superAdminUser();
        $course = $this->courseFor($superAdmin);
        $first = $this->questionIn($this->testOrganisation->id);
        $second = $this->questionIn($this->testOrganisation->id);

        $payload = $this->quizPayload($course, [
            ['question_id' => $first->id, 'points' => null],
            ['question_id' => $second->id, 'points' => 5],
        ]);

        $this->actingAs($superAdmin)
            ->postJson('/quizzes', $payload)
            ->assertStatus(201)
            ->assertJsonFragment(['title' => 'Laravel Basics Quiz']);

        $quiz = Quiz::query()->where('title', 'Laravel Basics Quiz')->firstOrFail();

        expect($quiz->slug)->not->toBeEmpty()
            ->and($quiz->created_by)->toBe($superAdmin->id)
            ->and($quiz->questions()->orderBy('quiz_question.position')->pluck('questions.id')->all())
            ->toBe([$first->id, $second->id]);

        $this->assertDatabaseHas('quiz_question', [
            'quiz_id' => $quiz->id,
            'question_id' => $second->id,
            'position' => 2,
            'points' => 5,
        ]);
    });

    test('creating a quiz writes an audit log with question ids, order and points', function () {
        $actor = $this->adminUser();
        $course = $this->courseFor($actor);
        $first = $this->questionIn($this->testOrganisation->id);
        $second = $this->questionIn($this->testOrganisation->id);

        $this->actingAs($actor)
            ->postJson('/quizzes', $this->quizPayload($course, [
                ['question_id' => $second->id, 'points' => 3],
                ['question_id' => $first->id, 'points' => null],
            ]))
            ->assertStatus(201);

        $log = Log::query()
            ->where('action_id', Log::ACTION_CREATE_QUIZ)
            ->where('logged_in_user_id', $actor->id)
            ->first();

        expect($log)->not->toBeNull()
            ->and($log->data)->toHaveKey('questions')
            ->and(collect($log->data['questions'])->pluck('question_id')->all())
            ->toBe([$second->id, $first->id])
            ->and($log->data['questions'][0]['points'])->toBe(3);
    });

    test('slug, organisation_id and position in the request body are ignored', function () {
        $superAdmin = $this->superAdminUser();
        $course = $this->courseFor($superAdmin);
        $first = $this->questionIn($this->testOrganisation->id);
        $second = $this->questionIn($this->testOrganisation->id);
        $otherOrganisationId = $this->otherOrganisationId();

        $payload = $this->quizPayload($course, [
            ['question_id' => $first->id, 'points' => null, 'position' => 9],
            ['question_id' => $second->id, 'points' => null, 'position' => 1],
        ], [
            'slug' => 'smuggled-slug',
            'organisation_id' => $otherOrganisationId,
        ]);

        $this->actingAs($superAdmin)
            ->postJson('/quizzes', $payload)
            ->assertStatus(201);

        $quiz = Quiz::query()->where('title', 'Laravel Basics Quiz')->firstOrFail();

        expect($quiz->slug)->not->toBe('smuggled-slug')
            ->and($quiz->organisation_id)->toBe($this->testOrganisation->id);

        $this->assertDatabaseHas('quiz_question', [
            'quiz_id' => $quiz->id,
            'question_id' => $first->id,
            'position' => 1,
        ]);

        $this->assertDatabaseHas('quiz_question', [
            'quiz_id' => $quiz->id,
            'question_id' => $second->id,
            'position' => 2,
        ]);
    });

    test('creating a quiz is atomic when attaching questions fails', function () {
        $superAdmin = $this->superAdminUser();
        $course = $this->courseFor($superAdmin);
        $question = $this->questionIn($this->testOrganisation->id);

        Quiz::created(function () {
            throw new RuntimeException('Simulated failure after the quiz row is written.');
        });

        $this->actingAs($superAdmin)
            ->postJson('/quizzes', $this->quizPayload($course, [
                ['question_id' => $question->id, 'points' => null],
            ]))
            ->assertStatus(500);

        $this->assertDatabaseMissing('quizzes', ['title' => 'Laravel Basics Quiz']);
        $this->assertDatabaseCount('quiz_question', 0);
    });

    test('user without permission cannot create a quiz', function () {
        $user = $this->normalUser();
        $course = $this->courseFor($user);
        $question = $this->questionIn($this->testOrganisation->id);

        $this->actingAs($user)
            ->postJson('/quizzes', $this->quizPayload($course, [
                ['question_id' => $question->id, 'points' => null],
            ]))
            ->assertStatus(403);
    });

    test('store fails validation when title is missing', function () {
        $superAdmin = $this->superAdminUser();
        $course = $this->courseFor($superAdmin);
        $question = $this->questionIn($this->testOrganisation->id);

        $this->actingAs($superAdmin)
            ->postJson('/quizzes', $this->quizPayload($course, [
                ['question_id' => $question->id, 'points' => null],
            ], ['title' => null]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    });

    test('store fails validation when pass mark is outside 1 to 100', function () {
        $superAdmin = $this->superAdminUser();
        $course = $this->courseFor($superAdmin);
        $question = $this->questionIn($this->testOrganisation->id);

        foreach ([0, 101] as $passMark) {
            $this->actingAs($superAdmin)
                ->postJson('/quizzes', $this->quizPayload($course, [
                    ['question_id' => $question->id, 'points' => null],
                ], ['pass_mark_percentage' => $passMark]))
                ->assertStatus(422)
                ->assertJsonValidationErrors(['pass_mark_percentage']);
        }
    });

    test('store fails validation when course_id is missing', function () {
        $superAdmin = $this->superAdminUser();
        $course = $this->courseFor($superAdmin);
        $question = $this->questionIn($this->testOrganisation->id);

        $this->actingAs($superAdmin)
            ->postJson('/quizzes', $this->quizPayload($course, [
                ['question_id' => $question->id, 'points' => null],
            ], ['course_id' => null]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['course_id']);
    });

    test('store succeeds with optional fields left null', function () {
        $superAdmin = $this->superAdminUser();
        $course = $this->courseFor($superAdmin);
        $question = $this->questionIn($this->testOrganisation->id);

        $this->actingAs($superAdmin)
            ->postJson('/quizzes', $this->quizPayload($course, [
                ['question_id' => $question->id, 'points' => null],
            ], [
                'description' => null,
                'time_limit_minutes' => null,
                'max_attempts' => null,
                'published_at' => null,
            ]))
            ->assertStatus(201);

        $this->assertDatabaseHas('quizzes', [
            'title' => 'Laravel Basics Quiz',
            'description' => null,
            'time_limit_minutes' => null,
            'max_attempts' => null,
            'published_at' => null,
        ]);
    });
});

describe('show', function () {
    test('authenticated user with permission can view a quiz', function () {
        $superAdmin = $this->superAdminUser();
        $quiz = $this->quizFor($superAdmin, [], 2);

        $this->actingAs($superAdmin)
            ->get("/quizzes/{$quiz->id}")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Quizzes/Show')
                ->has('quiz')
                ->where('canEdit', true)
            );
    });

    test('unauthenticated user cannot view a quiz', function () {
        $quiz = Quiz::factory()->create();

        $this->get("/quizzes/{$quiz->id}")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view a quiz', function () {
        $user = $this->userWithNoPermissions();
        $quiz = $this->quizFor($user);

        $this->actingAs($user)
            ->get("/quizzes/{$quiz->id}")
            ->assertStatus(403);
    });

    test('show returns 404 for a non-existent quiz', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/quizzes/99999')
            ->assertStatus(404);
    });
});

describe('edit', function () {
    test('authenticated user with permission can view edit form', function () {
        $superAdmin = $this->superAdminUser();
        $quiz = $this->quizFor($superAdmin, [], 2);

        $this->actingAs($superAdmin)
            ->get("/quizzes/{$quiz->id}/edit")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Quizzes/Edit')
                ->has('quiz')
                ->has('courses')
                ->has('questionOptions')
            );
    });

    test('unauthenticated user cannot view edit form', function () {
        $quiz = Quiz::factory()->create();

        $this->get("/quizzes/{$quiz->id}/edit")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view edit form', function () {
        $user = $this->normalUser();
        $quiz = $this->quizFor($user);

        $this->actingAs($user)
            ->get("/quizzes/{$quiz->id}/edit")
            ->assertStatus(403);
    });
});

describe('update', function () {
    test('authenticated user with permission can update a quiz', function () {
        $superAdmin = $this->superAdminUser();
        $quiz = $this->quizFor($superAdmin, ['title' => 'Old Title'], 1);

        $payload = $this->quizPayload($quiz->course, [
            ['question_id' => $quiz->questions()->first()->id, 'points' => null],
        ], ['title' => 'New Title']);

        $this->actingAs($superAdmin)
            ->putJson("/quizzes/{$quiz->id}", $payload)
            ->assertStatus(200)
            ->assertJsonFragment(['title' => 'New Title']);

        $this->assertDatabaseHas('quizzes', [
            'id' => $quiz->id,
            'title' => 'New Title',
            'updated_by' => $superAdmin->id,
        ]);
    });

    test('reordering and removing questions rewrites the pivot rows and positions', function () {
        $superAdmin = $this->superAdminUser();
        $quiz = $this->quizFor($superAdmin, [], 3);

        [$a, $b, $c] = $quiz->questions()->orderBy('quiz_question.position')->pluck('questions.id')->all();

        $this->actingAs($superAdmin)
            ->putJson("/quizzes/{$quiz->id}", $this->quizPayload($quiz->course, [
                ['question_id' => $c, 'points' => 4],
                ['question_id' => $a, 'points' => null],
            ]))
            ->assertStatus(200);

        expect($quiz->questions()->orderBy('quiz_question.position')->pluck('questions.id')->all())
            ->toBe([$c, $a]);

        $this->assertDatabaseHas('quiz_question', [
            'quiz_id' => $quiz->id,
            'question_id' => $c,
            'position' => 1,
            'points' => 4,
        ]);

        $this->assertDatabaseMissing('quiz_question', [
            'quiz_id' => $quiz->id,
            'question_id' => $b,
        ]);
    });

    test('updating a quiz is atomic when the update fails', function () {
        $superAdmin = $this->superAdminUser();
        $quiz = $this->quizFor($superAdmin, ['title' => 'Original'], 2);

        $before = $quiz->questions()->orderBy('quiz_question.position')->pluck('questions.id')->all();
        $replacement = $this->questionIn($this->testOrganisation->id);

        Quiz::updated(function () {
            throw new RuntimeException('Simulated failure after the quiz row is updated.');
        });

        $this->actingAs($superAdmin)
            ->putJson("/quizzes/{$quiz->id}", $this->quizPayload($quiz->course, [
                ['question_id' => $replacement->id, 'points' => null],
            ], ['title' => 'Changed']))
            ->assertStatus(500);

        $this->assertDatabaseHas('quizzes', ['id' => $quiz->id, 'title' => 'Original']);

        expect($quiz->questions()->orderBy('quiz_question.position')->pluck('questions.id')->all())
            ->toBe($before);
    });

    test('user without permission cannot update a quiz', function () {
        $user = $this->normalUser();
        $quiz = $this->quizFor($user, [], 1);

        $this->actingAs($user)
            ->putJson("/quizzes/{$quiz->id}", $this->quizPayload($quiz->course, [
                ['question_id' => $quiz->questions()->first()->id, 'points' => null],
            ]))
            ->assertStatus(403);
    });

    test('patch verb also updates a quiz', function () {
        $superAdmin = $this->superAdminUser();
        $quiz = $this->quizFor($superAdmin, [], 1);

        $this->actingAs($superAdmin)
            ->patchJson("/quizzes/{$quiz->id}", $this->quizPayload($quiz->course, [
                ['question_id' => $quiz->questions()->first()->id, 'points' => null],
            ], ['pass_mark_percentage' => 85]))
            ->assertStatus(200);

        $this->assertDatabaseHas('quizzes', [
            'id' => $quiz->id,
            'pass_mark_percentage' => 85,
        ]);
    });

    test('logs quiz updates with actor id', function () {
        $actor = $this->adminUser();
        $quiz = $this->quizFor($actor, ['title' => 'Old Title'], 1);

        $this->actingAs($actor)
            ->putJson("/quizzes/{$quiz->id}", $this->quizPayload($quiz->course, [
                ['question_id' => $quiz->questions()->first()->id, 'points' => null],
            ], ['title' => 'New Title']))
            ->assertOk();

        $log = Log::query()
            ->where('action_id', Log::ACTION_UPDATE_QUIZ)
            ->where('logged_in_user_id', $actor->id)
            ->first();

        expect($log)->not->toBeNull()
            ->and($log->data)->toHaveKeys(['before', 'after']);
    });
});

describe('destroy', function () {
    test('authenticated user with permission can soft delete a quiz', function () {
        $superAdmin = $this->superAdminUser();
        $quiz = $this->quizFor($superAdmin, [], 1);

        $this->actingAs($superAdmin)
            ->deleteJson("/quizzes/{$quiz->id}")
            ->assertStatus(204);

        $this->assertSoftDeleted('quizzes', ['id' => $quiz->id]);

        expect($quiz->fresh()->deleted_by)->toBe($superAdmin->id);
    });

    test('user without permission cannot soft delete a quiz', function () {
        $user = $this->normalUser();
        $quiz = $this->quizFor($user);

        $this->actingAs($user)
            ->deleteJson("/quizzes/{$quiz->id}")
            ->assertStatus(403);
    });

    test('destroy returns 404 for a non-existent quiz', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->deleteJson('/quizzes/99999')
            ->assertStatus(404);
    });
});

describe('restore', function () {
    test('authenticated user with permission can restore a soft-deleted quiz', function () {
        $superAdmin = $this->superAdminUser();
        $quiz = $this->quizFor($superAdmin);
        $quiz->delete();

        $this->actingAs($superAdmin)
            ->postJson("/quizzes/{$quiz->id}/restore")
            ->assertStatus(204);

        $this->assertDatabaseHas('quizzes', [
            'id' => $quiz->id,
            'deleted_at' => null,
            'restored_by' => $superAdmin->id,
        ]);

        expect($quiz->fresh()->restored_at)->not->toBeNull();
    });

    test('user without permission cannot restore a quiz', function () {
        $user = $this->normalUser();
        $quiz = $this->quizFor($user);
        $quiz->delete();

        $this->actingAs($user)
            ->postJson("/quizzes/{$quiz->id}/restore")
            ->assertStatus(403);
    });

    test('restore returns 404 for a quiz that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();
        $quiz = $this->quizFor($superAdmin);

        $this->actingAs($superAdmin)
            ->postJson("/quizzes/{$quiz->id}/restore")
            ->assertStatus(404);
    });
});

describe('force delete', function () {
    test('authenticated user with permission can force delete a quiz and its pivot rows', function () {
        $superAdmin = $this->superAdminUser();
        $quiz = $this->quizFor($superAdmin, [], 2);
        $quiz->delete();

        $this->actingAs($superAdmin)
            ->deleteJson("/quizzes/{$quiz->id}/force")
            ->assertStatus(204);

        $this->assertDatabaseMissing('quizzes', ['id' => $quiz->id]);
        $this->assertDatabaseMissing('quiz_question', ['quiz_id' => $quiz->id]);
    });

    test('user without permission cannot force delete a quiz', function () {
        $user = $this->normalUser();
        $quiz = $this->quizFor($user);
        $quiz->delete();

        $this->actingAs($user)
            ->deleteJson("/quizzes/{$quiz->id}/force")
            ->assertStatus(403);
    });

    test('force delete returns 404 for a quiz that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();
        $quiz = $this->quizFor($superAdmin);

        $this->actingAs($superAdmin)
            ->deleteJson("/quizzes/{$quiz->id}/force")
            ->assertStatus(404);
    });
});

describe('bulk delete', function () {
    test('authenticated user with permission can bulk soft delete quizzes', function () {
        $superAdmin = $this->superAdminUser();

        $quizzes = collect([
            $this->quizFor($superAdmin),
            $this->quizFor($superAdmin),
            $this->quizFor($superAdmin),
        ]);
        $ids = $quizzes->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson('/quizzes/bulk/delete', ['ids' => $ids])
            ->assertStatus(200)
            ->assertJson([
                'deleted' => $ids,
                'skipped' => [],
            ]);

        foreach ($ids as $id) {
            $this->assertSoftDeleted('quizzes', ['id' => $id]);
        }
    });

    test('bulk delete fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/quizzes/bulk/delete', ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk delete caps ids at 100', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/quizzes/bulk/delete', ['ids' => range(1, 101)])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk delete skips non-existent ids', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/quizzes/bulk/delete', ['ids' => [99999]])
            ->assertStatus(200)
            ->assertJson([
                'deleted' => [],
                'skipped' => [99999],
            ]);
    });

    test('user without permission cannot bulk delete quizzes', function () {
        $user = $this->normalUser();
        $quiz = $this->quizFor($user);

        $this->actingAs($user)
            ->postJson('/quizzes/bulk/delete', ['ids' => [$quiz->id]])
            ->assertStatus(403);
    });
});

describe('bulk restore', function () {
    test('authenticated user with permission can bulk restore quizzes', function () {
        $superAdmin = $this->superAdminUser();

        $quizzes = collect([
            $this->quizFor($superAdmin),
            $this->quizFor($superAdmin),
        ]);
        $quizzes->each->delete();
        $ids = $quizzes->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson('/quizzes/bulk/restore', ['ids' => $ids])
            ->assertStatus(200)
            ->assertJson([
                'restored' => $ids,
                'skipped' => [],
            ]);

        foreach ($ids as $id) {
            $this->assertDatabaseHas('quizzes', [
                'id' => $id,
                'deleted_at' => null,
            ]);
        }
    });

    test('bulk restore fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/quizzes/bulk/restore', ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk restore caps ids at 100', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/quizzes/bulk/restore', ['ids' => range(1, 101)])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk restore skips non-existent ids', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/quizzes/bulk/restore', ['ids' => [99999]])
            ->assertStatus(200)
            ->assertJson([
                'restored' => [],
                'skipped' => [99999],
            ]);
    });

    test('user without permission cannot bulk restore quizzes', function () {
        $user = $this->normalUser();
        $quiz = $this->quizFor($user);
        $quiz->delete();

        $this->actingAs($user)
            ->postJson('/quizzes/bulk/restore', ['ids' => [$quiz->id]])
            ->assertStatus(403);
    });
});

describe('soft delete scoping', function () {
    test('index does not return soft-deleted quizzes', function () {
        $superAdmin = $this->superAdminUser();

        $this->quizFor($superAdmin, ['title' => 'Visible']);
        $trashed = $this->quizFor($superAdmin, ['title' => 'Trashed']);
        $trashed->delete();

        $this->actingAs($superAdmin)
            ->get('/quizzes')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Quizzes/Index')
                ->where('quizzes.meta.total', 1)
                ->where('quizzes.data.0.title', 'Visible')
            );
    });

    test('show returns 404 for a soft-deleted quiz', function () {
        $superAdmin = $this->superAdminUser();
        $quiz = $this->quizFor($superAdmin);
        $quiz->delete();

        $this->actingAs($superAdmin)
            ->get("/quizzes/{$quiz->id}")
            ->assertStatus(404);
    });
});
