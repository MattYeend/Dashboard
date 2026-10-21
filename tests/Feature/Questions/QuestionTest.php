<?php

declare(strict_types=1);

use App\Models\Course;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\Concerns\CreatesUsers;

uses(
    LazilyRefreshDatabase::class,
    CreatesUsers::class,
);

function validQuestionPayload(Course $course): array
{
    return [
        'questionable_type' => 'course',
        'questionable_id' => $course->id,
        'question' => 'Which trait enables soft deleting?',
        'type' => 'short_text',
        'points' => 1,
        'is_required' => true,
    ];
}

describe('index', function () {
    test('user without permission cannot list questions', function () {
        $user = $this->userWithNoPermissions();

        $this->actingAs($user)
            ->get(route('questions.index'))
            ->assertForbidden();
    });
});

describe('store', function () {
    test('authenticated user with permission can create a question and the creator is recorded', function () {
        $superAdmin = $this->superAdminUser();
        $course = Course::factory()->create();

        $this->actingAs($superAdmin)
            ->post(route('questions.store'), validQuestionPayload($course))
            ->assertRedirect();

        $this->assertDatabaseHas('questions', [
            'questionable_type' => 'course',
            'questionable_id' => $course->id,
            'created_by' => $superAdmin->id,
        ]);
    });

    test('store saves the morph alias rather than a class name', function () {
        $superAdmin = $this->superAdminUser();
        $course = Course::factory()->create();

        $this->actingAs($superAdmin)
            ->post(route('questions.store'), validQuestionPayload($course));

        expect(Question::query()->latest('id')->first()->getRawOriginal('questionable_type'))
            ->toBe('course');
    });

    test('store fails validation when questionable_type is not registered', function () {
        $superAdmin = $this->superAdminUser();
        $course = Course::factory()->create();

        $this->actingAs($superAdmin)
            ->post(route('questions.store'), [
                ...validQuestionPayload($course),
                'questionable_type' => User::class,
            ])
            ->assertSessionHasErrors('questionable_type');
    });

    test('store fails validation when questionable_id does not exist', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->post(route('questions.store'), [
                'questionable_type' => 'course',
                'questionable_id' => 999999,
                'question' => 'Does this exist?',
                'type' => 'true_false',
                'points' => 1,
            ])
            ->assertSessionHasErrors('questionable_id');
    });

    test('store ignores audit columns sent in the request', function () {
        $superAdmin = $this->superAdminUser();
        $other = User::factory()->create();
        $course = Course::factory()->create();

        $this->actingAs($superAdmin)
            ->post(route('questions.store'), [
                ...validQuestionPayload($course),
                'created_by' => $other->id,
            ]);

        expect(Question::query()->latest('id')->first()->created_by)->toBe($superAdmin->id);
    });
});

describe('update', function () {
    test('update does not allow the owner to change', function () {
        $superAdmin = $this->superAdminUser();
        $question = Question::factory()->create();
        $other = Course::factory()->create();

        $this->actingAs($superAdmin)
            ->put(route('questions.update', $question), [
                'question' => 'Updated wording',
                'type' => 'short_text',
                'points' => 2,
                'questionable_type' => 'course',
                'questionable_id' => $other->id,
            ])
            ->assertRedirect();

        $fresh = $question->fresh();

        expect($fresh->question)->toBe('Updated wording')
            ->and($fresh->questionable_id)->toBe($question->questionable_id)
            ->and($fresh->updated_by)->toBe($superAdmin->id);
    });
});

describe('destroy', function () {
    test('authenticated user with permission can soft delete a question and deleted_by is recorded', function () {
        $superAdmin = $this->superAdminUser();
        $question = Question::factory()->create();

        $this->actingAs($superAdmin)
            ->delete(route('questions.destroy', $question))
            ->assertRedirect();

        $this->assertSoftDeleted('questions', ['id' => $question->id]);

        expect(Question::find($question->id))->toBeNull()
            ->and(Question::withTrashed()->find($question->id)->deleted_by)->toBe($superAdmin->id);
    });
});

describe('restore', function () {
    test('authenticated user with permission can restore a question and restored_by is recorded', function () {
        $superAdmin = $this->superAdminUser();
        $question = Question::factory()->create();
        $question->delete();

        $this->actingAs($superAdmin)
            ->post(route('questions.restore', $question->id))
            ->assertRedirect();

        expect(Question::find($question->id)->restored_by)->toBe($superAdmin->id);
    });
});

describe('force delete', function () {
    test('force delete is refused for a question used in a quiz', function () {
        $superAdmin = $this->superAdminUser();
        $question = Question::factory()->create();
        $quiz = Quiz::factory()->create();

        QuizQuestion::factory()
            ->forQuiz($quiz)
            ->forQuestion($question)
            ->create();

        $question->delete();

        $this->actingAs($superAdmin)
            ->delete(route('questions.force-delete', $question->id))
            ->assertSessionHas('error');

        expect(Question::withTrashed()->find($question->id))->not->toBeNull();
    });
});

describe('export', function () {
    test('user without permission cannot export questions', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->get(route('questions.export'))
            ->assertForbidden();
    });

    test('export neutralises formula injection in the csv', function () {
        $superAdmin = $this->superAdminUser();

        Question::factory()->create(['question' => '=HYPERLINK("https://example.test")']);

        $content = $this->actingAs($superAdmin)
            ->get(route('questions.export'))
            ->assertOk()
            ->streamedContent();

        expect($content)->toContain("'=HYPERLINK");
    });
});

describe('import', function () {
    test('user without permission cannot import questions', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->post(route('questions.import'), [
                'file' => UploadedFile::fake()->createWithContent('questions.csv', "a,b\n1,2\n"),
            ])
            ->assertForbidden();
    });

    test('import reports missing required columns', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->post(route('questions.import'), [
                'file' => UploadedFile::fake()->createWithContent(
                    'questions.csv',
                    "question\nWhat is Laravel?\n"
                ),
            ])
            ->assertSessionHas('import_skipped');

        expect(Question::query()->count())->toBe(0);
    });

    test('import creates valid rows and skips invalid ones', function () {
        $superAdmin = $this->superAdminUser();
        $course = Course::factory()->create();

        $csv = "questionable_type,questionable_id,question,type,points\n"
            ."course,{$course->id},What does MVC stand for?,short_text,1\n"
            ."user,1,Unregistered owner type,short_text,1\n";

        $this->actingAs($superAdmin)
            ->post(route('questions.import'), [
                'file' => UploadedFile::fake()->createWithContent('questions.csv', $csv),
            ])
            ->assertRedirect();

        expect(Question::query()->count())->toBe(1);
    });
});
