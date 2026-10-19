<?php

declare(strict_types=1);

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Organisation;
use App\Services\Lessons\PositionService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\CreatesUsers;

uses(
    LazilyRefreshDatabase::class,
    CreatesUsers::class,
);

/**
 * Build a valid create payload for a course.
 *
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function lessonPayload(int $courseId, array $overrides = []): array
{
    return array_merge([
        'course_id' => $courseId,
        'title' => 'Routing basics',
        'summary' => 'How Laravel maps URLs to controllers.',
        'content' => 'Routes are defined in routes/web.php.',
        'duration_minutes' => 20,
        'is_preview' => false,
    ], $overrides);
}

describe('index', function () {
    test('authenticated user with permission can list lessons', function () {
        $superAdmin = $this->superAdminUser();

        Lesson::factory()->count(3)->create();

        $this->actingAs($superAdmin)
            ->get('/lessons')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Lessons/Index')
                ->has('lessons')
            );
    });

    test('unauthenticated user is redirected to login', function () {
        $this->get('/lessons')
            ->assertRedirect('/login');
    });

    test('user without view any lessons receives 403', function () {
        $user = $this->userWithNoPermissions();

        $this->actingAs($user)
            ->get('/lessons')
            ->assertStatus(403);
    });
});

describe('create', function () {
    test('authenticated user with permission can view create form', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/lessons/create')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Lessons/Create')
                ->has('course_options')
            );
    });

    test('unauthenticated user is redirected to login', function () {
        $this->get('/lessons/create')
            ->assertRedirect('/login');
    });

    test('user without permission receives 403', function () {
        $user = $this->userWithNoPermissions();

        $this->actingAs($user)
            ->get('/lessons/create')
            ->assertStatus(403);
    });

    test('a learner cannot open the create form', function () {
        $this->actingAs($this->learnerUser())
            ->get('/lessons/create')
            ->assertStatus(403);
    });
});

describe('store', function () {
    test('authenticated user with permission can create a lesson', function () {
        $superAdmin = $this->superAdminUser();
        $course = Course::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson('/lessons', lessonPayload($course->id))
            ->assertStatus(201)
            ->assertJsonFragment(['title' => 'Routing basics']);

        $this->assertDatabaseHas('lessons', [
            'course_id' => $course->id,
            'organisation_id' => $course->organisation_id,
            'title' => 'Routing basics',
            'slug' => 'routing-basics',
        ]);
    });

    test('unauthenticated user cannot create a lesson', function () {
        $course = Course::factory()->create();

        $this->postJson('/lessons', lessonPayload($course->id))
            ->assertStatus(401);
    });

    test('user without permission cannot create a lesson', function () {
        $user = $this->learnerUser();
        $course = Course::factory()->create();

        $this->actingAs($user)
            ->postJson('/lessons', lessonPayload($course->id))
            ->assertStatus(403);
    });

    test('store fails validation when title is missing', function () {
        $superAdmin = $this->superAdminUser();
        $course = Course::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson('/lessons', lessonPayload($course->id, ['title' => '']))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    });

    test('store fails validation when course_id is missing', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/lessons', ['title' => 'No course'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['course_id']);
    });

    test('store fails validation when duration is out of range', function () {
        $superAdmin = $this->superAdminUser();
        $course = Course::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson('/lessons', lessonPayload($course->id, ['duration_minutes' => 1441]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['duration_minutes']);
    });
});

describe('positions', function () {
    test('creating a lesson assigns the next position in the course', function () {
        $superAdmin = $this->superAdminUser();
        $course = Course::factory()->create();

        foreach ([1, 2, 3] as $expected) {
            $id = $this->actingAs($superAdmin)
                ->postJson('/lessons', lessonPayload($course->id, ['title' => "Lesson {$expected}"]))
                ->assertStatus(201)
                ->json('id');

            expect(Lesson::query()->findOrFail($id)->position)->toBe($expected);
        }
    });

    test('positions are counted per course', function () {
        $superAdmin = $this->superAdminUser();
        $courseOne = Course::factory()->create();
        $courseTwo = Course::factory()->create();

        Lesson::factory()->count(2)->sequence(['position' => 1], ['position' => 2])
            ->create(['course_id' => $courseOne->id]);

        $id = $this->actingAs($superAdmin)
            ->postJson('/lessons', lessonPayload($courseTwo->id))
            ->assertStatus(201)
            ->json('id');

        expect(Lesson::query()->findOrFail($id)->position)->toBe(1);
    });

    test('trashed lessons still count so a position is never reused', function () {
        $superAdmin = $this->superAdminUser();
        $course = Course::factory()->create();

        Lesson::factory()->deleted()->create(['course_id' => $course->id, 'position' => 5]);

        $id = $this->actingAs($superAdmin)
            ->postJson('/lessons', lessonPayload($course->id))
            ->assertStatus(201)
            ->json('id');

        expect(Lesson::query()->findOrFail($id)->position)->toBe(6);
    });

    test('the next position is read inside a transaction opened by the create flow', function () {
        $superAdmin = $this->superAdminUser();
        $course = Course::factory()->create();

        $baseline = DB::transactionLevel();
        $levels = [];

        $this->mock(PositionService::class, function ($mock) use (&$levels): void {
            $mock->shouldReceive('next')
                ->once()
                ->andReturnUsing(function () use (&$levels): int {
                    $levels[] = DB::transactionLevel();

                    return 1;
                });
        });

        $this->actingAs($superAdmin)
            ->postJson('/lessons', lessonPayload($course->id))
            ->assertStatus(201);

        expect($levels)->toHaveCount(1)
            ->and($levels[0])->toBeGreaterThan($baseline);
    });

    test('two creates in the same course never share a position', function () {
        $superAdmin = $this->superAdminUser();
        $course = Course::factory()->create();

        $positions = collect(range(1, 10))->map(function (int $number) use ($superAdmin, $course): int {
            $id = $this->actingAs($superAdmin)
                ->postJson('/lessons', lessonPayload($course->id, ['title' => "Lesson {$number}"]))
                ->assertStatus(201)
                ->json('id');

            return Lesson::query()->findOrFail($id)->position;
        });

        expect($positions->unique()->count())->toBe(10)
            ->and($positions->all())->toBe(range(1, 10));
    });
});

describe('mass assignment', function () {
    test('position, slug, organisation_id and created_by in the body are ignored', function () {
        $actor = $this->adminUser();
        $other = $this->normalUser();
        $course = Course::factory()->create();

        $organisationB = $this->otherOrganisation();

        $id = $this->actingAs($actor)
            ->postJson('/lessons', lessonPayload($course->id, [
                'title' => 'Genuine title',
                'position' => 99,
                'slug' => 'attacker-chosen-slug',
                'organisation_id' => $organisationB->id,
                'created_by' => $other->id,
            ]))
            ->assertStatus(201)
            ->json('id');

        $lesson = Lesson::query()->findOrFail($id);

        expect($lesson->position)->toBe(1)
            ->and($lesson->slug)->toBe('genuine-title')
            ->and($lesson->organisation_id)->toBe($course->organisation_id)
            ->and($lesson->created_by)->toBe($actor->id);
    });

    test('organisation_id, position and course_id cannot be forced through an update body', function () {
        $actor = $this->adminUser();
        $lesson = Lesson::factory()->create(['position' => 3]);

        $organisationB = $this->otherOrganisation();

        $this->actingAs($actor)
            ->putJson("/lessons/{$lesson->id}", [
                'title' => 'Renamed',
                'position' => 99,
                'organisation_id' => $organisationB->id,
            ])
            ->assertStatus(200);

        $lesson->refresh();

        expect($lesson->title)->toBe('Renamed')
            ->and($lesson->position)->toBe(3)
            ->and($lesson->organisation_id)->not->toBe($organisationB->id);
    });
});

describe('content sanitising', function () {
    test('script tags in content are stripped on create', function () {
        $superAdmin = $this->superAdminUser();
        $course = Course::factory()->create();

        $id = $this->actingAs($superAdmin)
            ->postJson('/lessons', lessonPayload($course->id, [
                'content' => '<p>Hello</p><script>alert(1)</script>',
            ]))
            ->assertStatus(201)
            ->json('id');

        $content = Lesson::query()->findOrFail($id)->content;

        expect($content)->not->toContain('<script')
            ->and($content)->not->toContain('</script')
            ->and($content)->not->toContain('<p>');
    });

    test('script tags in content are stripped on update', function () {
        $superAdmin = $this->superAdminUser();
        $lesson = Lesson::factory()->create();

        $this->actingAs($superAdmin)
            ->putJson("/lessons/{$lesson->id}", [
                'content' => '<img src=x onerror=alert(1)><script>alert(1)</script>Safe text',
            ])
            ->assertStatus(200);

        $content = $lesson->fresh()->content;

        expect($content)->not->toContain('<script')
            ->and($content)->not->toContain('onerror')
            ->and($content)->toContain('Safe text');
    });

    test('script tags in the title and summary are stripped', function () {
        $superAdmin = $this->superAdminUser();
        $course = Course::factory()->create();

        $id = $this->actingAs($superAdmin)
            ->postJson('/lessons', lessonPayload($course->id, [
                'title' => 'Title<script>alert(1)</script>',
                'summary' => 'Summary<script>alert(1)</script>',
            ]))
            ->assertStatus(201)
            ->json('id');

        $lesson = Lesson::query()->findOrFail($id);

        expect($lesson->title)->not->toContain('<script')
            ->and($lesson->summary)->not->toContain('<script');
    });
});

describe('course validation', function () {
    test('course_id from another organisation is rejected with a 422', function () {
        $superAdmin = $this->superAdminUser();

        $foreignCourse = $this->inOrganisation(
            $this->otherOrganisation(),
            fn (Organisation $organisationB) => Course::factory()->create(['organisation_id' => $organisationB->id]),
        );

        $this->actingAs($superAdmin)
            ->postJson('/lessons', lessonPayload($foreignCourse->id))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['course_id']);

        $this->assertDatabaseMissing('lessons', ['course_id' => $foreignCourse->id]);
    });

    test('a trashed course is rejected with a 422', function () {
        $superAdmin = $this->superAdminUser();
        $trashedCourse = Course::factory()->create(['deleted_at' => now()]);

        $this->actingAs($superAdmin)
            ->postJson('/lessons', lessonPayload($trashedCourse->id))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['course_id']);
    });

    test('a course that does not exist is rejected with a 422', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/lessons', lessonPayload(99999))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['course_id']);
    });

    test('moving a lesson to a course in another organisation is rejected with a 422', function () {
        $superAdmin = $this->superAdminUser();
        $lesson = Lesson::factory()->create();

        $foreignCourse = $this->inOrganisation(
            $this->otherOrganisation(),
            fn (Organisation $organisationB) => Course::factory()->create(['organisation_id' => $organisationB->id]),
        );

        $this->actingAs($superAdmin)
            ->putJson("/lessons/{$lesson->id}", ['course_id' => $foreignCourse->id])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['course_id']);

        expect($lesson->fresh()->course_id)->toBe($lesson->course_id);
    });
});

describe('video url validation', function () {
    test('unsafe or unknown video urls are rejected with a 422', function (string $url) {
        $superAdmin = $this->superAdminUser();
        $course = Course::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson('/lessons', lessonPayload($course->id, ['video_url' => $url]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['video_url']);

        $this->assertDatabaseMissing('lessons', ['video_url' => $url]);
    })->with([
        'http' => ['http://www.youtube.com/watch?v=dQw4w9WgXcQ'],
        'javascript' => ['javascript:alert(1)'],
        'data' => ['data:text/html;base64,PHNjcmlwdD5hbGVydCgxKTwvc2NyaXB0Pg=='],
        'unknown host' => ['https://example.com/video.mp4'],
        'look-alike host' => ['https://www.youtube.com.evil.example/watch?v=dQw4w9WgXcQ'],
    ]);

    test('YouTube and Vimeo urls are accepted', function (string $url) {
        $superAdmin = $this->superAdminUser();
        $course = Course::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson('/lessons', lessonPayload($course->id, ['video_url' => $url]))
            ->assertStatus(201);

        $this->assertDatabaseHas('lessons', ['video_url' => $url]);
    })->with([
        'youtube watch' => ['https://www.youtube.com/watch?v=dQw4w9WgXcQ'],
        'youtu.be' => ['https://youtu.be/dQw4w9WgXcQ'],
        'vimeo' => ['https://vimeo.com/123456789'],
    ]);

    test('an unsafe video url is also rejected on update', function () {
        $superAdmin = $this->superAdminUser();
        $lesson = Lesson::factory()->create();

        $this->actingAs($superAdmin)
            ->putJson("/lessons/{$lesson->id}", ['video_url' => 'javascript:alert(1)'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['video_url']);
    });

    test('an array sent as video_url is a validation error, not a server error', function () {
        $superAdmin = $this->superAdminUser();
        $course = Course::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson('/lessons', lessonPayload($course->id, ['video_url' => ['https://youtu.be/x']]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['video_url']);
    });

    test('the show page exposes an embed url for a valid video', function () {
        $superAdmin = $this->superAdminUser();
        $lesson = Lesson::factory()->create(['video_url' => 'https://youtu.be/dQw4w9WgXcQ']);

        $this->actingAs($superAdmin)
            ->get("/lessons/{$lesson->id}")
            ->assertInertia(fn (Assert $page) => $page
                ->component('Lessons/Show')
                ->where('lesson.embed_url', 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ')
            );
    });
});

describe('show', function () {
    test('authenticated user with permission can view a lesson', function () {
        $superAdmin = $this->superAdminUser();
        $lesson = Lesson::factory()->create();

        $this->actingAs($superAdmin)
            ->get("/lessons/{$lesson->id}")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Lessons/Show')
                ->has('lesson')
                ->has('navigation')
                ->where('canEdit', true)
            );
    });

    test('unauthenticated user is redirected to login', function () {
        $lesson = Lesson::factory()->create();

        $this->get("/lessons/{$lesson->id}")
            ->assertRedirect('/login');
    });

    test('user without permission receives 403', function () {
        $user = $this->userWithNoPermissions();
        $lesson = Lesson::factory()->create();

        $this->actingAs($user)
            ->get("/lessons/{$lesson->id}")
            ->assertStatus(403);
    });

    test('show returns 404 for a non-existent lesson', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/lessons/99999')
            ->assertStatus(404);
    });

    test('a learner can view a published lesson but does not get the edit flag', function () {
        $learner = $this->learnerUser();
        $course = Course::factory()->create(['published_at' => now()->subDay()]);
        $lesson = Lesson::factory()->create([
            'course_id' => $course->id,
            'published_at' => now()->subDay(),
        ]);

        $this->actingAs($learner)
            ->get("/lessons/{$lesson->id}")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Lessons/Show')
                ->where('canEdit', false)
            );
    });
});

describe('learner visibility', function () {
    test('a learner cannot view a draft lesson', function () {
        $learner = $this->learnerUser();
        $course = Course::factory()->create(['published_at' => now()->subDay()]);
        $draft = Lesson::factory()->create(['course_id' => $course->id, 'published_at' => null]);

        $this->actingAs($learner)
            ->get("/lessons/{$draft->id}")
            ->assertStatus(403);
    });

    test('a learner cannot view a lesson scheduled for the future', function () {
        $learner = $this->learnerUser();
        $course = Course::factory()->create(['published_at' => now()->subDay()]);
        $scheduled = Lesson::factory()->create([
            'course_id' => $course->id,
            'published_at' => now()->addWeek(),
        ]);

        $this->actingAs($learner)
            ->get("/lessons/{$scheduled->id}")
            ->assertStatus(403);
    });

    test('a learner cannot view a published lesson in a draft course', function () {
        $learner = $this->learnerUser();
        $draftCourse = Course::factory()->create(['published_at' => null]);
        $lesson = Lesson::factory()->create([
            'course_id' => $draftCourse->id,
            'published_at' => now()->subDay(),
        ]);

        $this->actingAs($learner)
            ->get("/lessons/{$lesson->id}")
            ->assertStatus(403);
    });

    test('a learner cannot view a trashed lesson', function () {
        $learner = $this->learnerUser();
        $course = Course::factory()->create(['published_at' => now()->subDay()]);
        $trashed = Lesson::factory()->deleted()->create([
            'course_id' => $course->id,
            'published_at' => now()->subDay(),
        ]);

        $this->actingAs($learner)
            ->get("/lessons/{$trashed->id}")
            ->assertStatus(404);
    });

    test('an editor can view a draft lesson', function () {
        $editor = $this->lessonEditorUser();
        $draftCourse = Course::factory()->create(['published_at' => null]);
        $draft = Lesson::factory()->create(['course_id' => $draftCourse->id, 'published_at' => null]);

        $this->actingAs($editor)
            ->get("/lessons/{$draft->id}")
            ->assertStatus(200);
    });

    test('the index only lists published lessons in published courses for a learner', function () {
        $learner = $this->learnerUser();
        $publishedCourse = Course::factory()->create(['published_at' => now()->subDay()]);
        $draftCourse = Course::factory()->create(['published_at' => null]);

        $visible = Lesson::factory()->create([
            'course_id' => $publishedCourse->id,
            'published_at' => now()->subDay(),
        ]);
        Lesson::factory()->create(['course_id' => $publishedCourse->id, 'published_at' => null]);
        Lesson::factory()->create(['course_id' => $publishedCourse->id, 'published_at' => now()->addWeek()]);
        Lesson::factory()->create(['course_id' => $draftCourse->id, 'published_at' => now()->subDay()]);
        Lesson::factory()->deleted()->create([
            'course_id' => $publishedCourse->id,
            'published_at' => now()->subDay(),
        ]);

        $this->actingAs($learner)
            ->get('/lessons')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Lessons/Index')
                ->has('lessons.data', 1)
                ->where('lessons.data.0.id', $visible->id)
            );
    });

    test('a learner cannot reveal trashed lessons with the trashed filter', function () {
        $learner = $this->learnerUser();
        $course = Course::factory()->create(['published_at' => now()->subDay()]);

        Lesson::factory()->create(['course_id' => $course->id, 'published_at' => now()->subDay()]);
        Lesson::factory()->deleted()->create([
            'course_id' => $course->id,
            'published_at' => now()->subDay(),
        ]);

        $this->actingAs($learner)
            ->get('/lessons?trashed=with')
            ->assertInertia(fn (Assert $page) => $page->has('lessons.data', 1));

        $this->actingAs($learner)
            ->get('/lessons?trashed=only')
            ->assertInertia(fn (Assert $page) => $page->has('lessons.data', 1));
    });

    test('neighbours never leak the titles of draft, scheduled or trashed lessons', function () {
        $learner = $this->learnerUser();
        $course = Course::factory()->create(['published_at' => now()->subDay()]);

        $first = Lesson::factory()->create([
            'course_id' => $course->id,
            'position' => 1,
            'title' => 'First lesson',
            'published_at' => now()->subDay(),
        ]);
        Lesson::factory()->create([
            'course_id' => $course->id,
            'position' => 2,
            'title' => 'Secret draft lesson',
            'published_at' => null,
        ]);
        Lesson::factory()->create([
            'course_id' => $course->id,
            'position' => 3,
            'title' => 'Secret scheduled lesson',
            'published_at' => now()->addWeek(),
        ]);
        Lesson::factory()->deleted()->create([
            'course_id' => $course->id,
            'position' => 4,
            'title' => 'Secret trashed lesson',
            'published_at' => now()->subDay(),
        ]);
        $last = Lesson::factory()->create([
            'course_id' => $course->id,
            'position' => 5,
            'title' => 'Last lesson',
            'published_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($learner)
            ->get("/lessons/{$first->id}")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->where('navigation.previous', null)
                ->where('navigation.next.id', $last->id)
                ->where('navigation.next.title', 'Last lesson')
            );

        $content = $response->getContent();

        expect($content)->not->toContain('Secret draft lesson')
            ->and($content)->not->toContain('Secret scheduled lesson')
            ->and($content)->not->toContain('Secret trashed lesson');
    });

    test('neighbours in an unpublished course are never exposed to a learner', function () {
        $learner = $this->learnerUser();
        $publishedCourse = Course::factory()->create(['published_at' => now()->subDay()]);
        $draftCourse = Course::factory()->create(['published_at' => null]);

        $lesson = Lesson::factory()->create([
            'course_id' => $publishedCourse->id,
            'position' => 1,
            'published_at' => now()->subDay(),
        ]);
        Lesson::factory()->create([
            'course_id' => $draftCourse->id,
            'position' => 2,
            'title' => 'Draft course lesson',
            'published_at' => now()->subDay(),
        ]);

        $this->actingAs($learner)
            ->get("/lessons/{$lesson->id}")
            ->assertInertia(fn (Assert $page) => $page->where('navigation.next', null));
    });

    test('an editor sees draft lessons as neighbours', function () {
        $editor = $this->lessonEditorUser();
        $course = Course::factory()->create(['published_at' => now()->subDay()]);

        $first = Lesson::factory()->create(['course_id' => $course->id, 'position' => 1]);
        $draft = Lesson::factory()->create([
            'course_id' => $course->id,
            'position' => 2,
            'title' => 'Draft lesson',
            'published_at' => null,
        ]);

        $this->actingAs($editor)
            ->get("/lessons/{$first->id}")
            ->assertInertia(fn (Assert $page) => $page
                ->where('navigation.next.id', $draft->id)
                ->where('navigation.next.title', 'Draft lesson')
            );
    });
});

describe('edit', function () {
    test('authenticated user with permission can view edit form', function () {
        $superAdmin = $this->superAdminUser();
        $lesson = Lesson::factory()->create();

        $this->actingAs($superAdmin)
            ->get("/lessons/{$lesson->id}/edit")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Lessons/Edit')
                ->has('lesson')
                ->has('course_options')
            );
    });

    test('unauthenticated user is redirected to login', function () {
        $lesson = Lesson::factory()->create();

        $this->get("/lessons/{$lesson->id}/edit")
            ->assertRedirect('/login');
    });

    test('a learner cannot view the edit form', function () {
        $lesson = Lesson::factory()->create();

        $this->actingAs($this->learnerUser())
            ->get("/lessons/{$lesson->id}/edit")
            ->assertStatus(403);
    });
});

describe('update', function () {
    test('authenticated user with permission can update a lesson', function () {
        $superAdmin = $this->superAdminUser();
        $lesson = Lesson::factory()->create(['title' => 'Old title']);

        $this->actingAs($superAdmin)
            ->putJson("/lessons/{$lesson->id}", ['title' => 'New title'])
            ->assertStatus(200)
            ->assertJsonFragment(['title' => 'New title']);

        expect($lesson->fresh()->updated_by)->toBe($superAdmin->id);
    });

    test('patch verb also updates a lesson', function () {
        $superAdmin = $this->superAdminUser();
        $lesson = Lesson::factory()->create();

        $this->actingAs($superAdmin)
            ->patchJson("/lessons/{$lesson->id}", ['summary' => 'New summary'])
            ->assertStatus(200)
            ->assertJsonFragment(['summary' => 'New summary']);
    });

    test('a learner cannot update a lesson', function () {
        $lesson = Lesson::factory()->create();

        $this->actingAs($this->learnerUser())
            ->putJson("/lessons/{$lesson->id}", ['title' => 'Hacked'])
            ->assertStatus(403);
    });

    test('omitted fields are not cleared on update', function () {
        $superAdmin = $this->superAdminUser();
        $lesson = Lesson::factory()->create(['summary' => 'Original summary']);

        $this->actingAs($superAdmin)
            ->putJson("/lessons/{$lesson->id}", ['title' => 'Updated title'])
            ->assertStatus(200);

        expect($lesson->fresh()->summary)->toBe('Original summary');
    });

    test('moving a lesson to another course in the same organisation puts it at the end', function () {
        $superAdmin = $this->superAdminUser();
        $target = Course::factory()->create();
        Lesson::factory()->create(['course_id' => $target->id, 'position' => 4]);
        $lesson = Lesson::factory()->create(['position' => 1]);

        $this->actingAs($superAdmin)
            ->putJson("/lessons/{$lesson->id}", ['course_id' => $target->id])
            ->assertStatus(200);

        $lesson->refresh();

        expect($lesson->course_id)->toBe($target->id)
            ->and($lesson->position)->toBe(5);
    });
});

describe('destroy', function () {
    test('soft delete sets deleted_by and deleted_at', function () {
        $admin = $this->adminUser();
        $lesson = Lesson::factory()->create();

        $this->actingAs($admin)
            ->deleteJson("/lessons/{$lesson->id}")
            ->assertStatus(204);

        $this->assertSoftDeleted('lessons', ['id' => $lesson->id]);
        $this->assertDatabaseHas('lessons', [
            'id' => $lesson->id,
            'deleted_by' => $admin->id,
        ]);
    });

    test('a learner cannot soft delete a lesson', function () {
        $lesson = Lesson::factory()->create();

        $this->actingAs($this->learnerUser())
            ->deleteJson("/lessons/{$lesson->id}")
            ->assertStatus(403);

        $this->assertNotSoftDeleted('lessons', ['id' => $lesson->id]);
    });

    test('destroy returns 404 for a non-existent lesson', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->deleteJson('/lessons/99999')
            ->assertStatus(404);
    });
});

describe('restore', function () {
    test('restore sets restored_by and restored_at and clears deleted_at', function () {
        $admin = $this->adminUser();
        $lesson = Lesson::factory()->deleted()->create();

        $this->actingAs($admin)
            ->postJson("/lessons/{$lesson->id}/restore")
            ->assertStatus(204);

        $restored = Lesson::query()->findOrFail($lesson->id);

        expect($restored->deleted_at)->toBeNull()
            ->and($restored->restored_by)->toBe($admin->id)
            ->and($restored->restored_at)->not->toBeNull();
    });

    test('a learner cannot restore a lesson', function () {
        $lesson = Lesson::factory()->deleted()->create();

        $this->actingAs($this->learnerUser())
            ->postJson("/lessons/{$lesson->id}/restore")
            ->assertStatus(403);
    });

    test('restore returns 404 for a lesson that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();
        $lesson = Lesson::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson("/lessons/{$lesson->id}/restore")
            ->assertStatus(404);
    });
});

describe('force delete', function () {
    test('authenticated user with permission can force delete a trashed lesson', function () {
        $superAdmin = $this->superAdminUser();
        $lesson = Lesson::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/lessons/{$lesson->id}/force")
            ->assertStatus(204);

        $this->assertDatabaseMissing('lessons', ['id' => $lesson->id]);
    });

    test('an editor without force delete lessons cannot force delete', function () {
        $lesson = Lesson::factory()->deleted()->create();

        $this->actingAs($this->lessonEditorUser())
            ->deleteJson("/lessons/{$lesson->id}/force")
            ->assertStatus(403);

        $this->assertDatabaseHas('lessons', ['id' => $lesson->id]);
    });

    test('force delete returns 404 for a lesson that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();
        $lesson = Lesson::factory()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/lessons/{$lesson->id}/force")
            ->assertStatus(404);
    });
});

describe('bulk delete', function () {
    test('an admin can bulk soft delete lessons', function () {
        $admin = $this->adminUser();
        $lessons = Lesson::factory()->count(3)->create();
        $ids = $lessons->pluck('id')->all();

        $this->actingAs($admin)
            ->postJson('/lessons/bulk/delete', ['ids' => $ids])
            ->assertStatus(200)
            ->assertJson(['deleted' => $ids, 'skipped' => []]);

        foreach ($ids as $id) {
            $this->assertSoftDeleted('lessons', ['id' => $id]);
        }
    });

    test('a non-admin with delete lessons is blocked by the bulkDelete ability', function () {
        $lessons = Lesson::factory()->count(2)->create();

        $this->actingAs($this->lessonEditorUser())
            ->postJson('/lessons/bulk/delete', ['ids' => $lessons->pluck('id')->all()])
            ->assertStatus(403);

        foreach ($lessons as $lesson) {
            $this->assertNotSoftDeleted('lessons', ['id' => $lesson->id]);
        }
    });

    test('a learner cannot bulk delete lessons', function () {
        $lessons = Lesson::factory()->count(2)->create();

        $this->actingAs($this->learnerUser())
            ->postJson('/lessons/bulk/delete', ['ids' => $lessons->pluck('id')->all()])
            ->assertStatus(403);
    });

    test('already trashed and non-existent ids are skipped', function () {
        $admin = $this->adminUser();
        $active = Lesson::factory()->create();
        $trashed = Lesson::factory()->deleted()->create();

        $this->actingAs($admin)
            ->postJson('/lessons/bulk/delete', ['ids' => [$active->id, $trashed->id, 99999]])
            ->assertStatus(200)
            ->assertJson([
                'deleted' => [$active->id],
                'skipped' => [$trashed->id, 99999],
            ]);
    });

    test('fails validation with an empty ids array', function () {
        $admin = $this->adminUser();

        $this->actingAs($admin)
            ->postJson('/lessons/bulk/delete', ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('ids are capped at 100', function () {
        $admin = $this->adminUser();

        $this->actingAs($admin)
            ->postJson('/lessons/bulk/delete', ['ids' => range(1, 101)])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);

        $this->actingAs($admin)
            ->postJson('/lessons/bulk/delete', ['ids' => range(1, 100)])
            ->assertStatus(200);
    });

    test('a failed per-item authorisation rolls the whole batch back', function () {
        $admin = $this->adminUser();
        $deletable = Lesson::factory()->create();
        $other = Lesson::factory()->create();

        // Force the per-item 'delete' check to fail for one lesson only.
        Gate::before(function ($user, string $ability, array $arguments) use ($other) {
            $model = $arguments[0] ?? null;

            if ($ability === 'delete' && $model instanceof Lesson && $model->is($other)) {
                return false;
            }

            return null;
        });

        $this->actingAs($admin)
            ->postJson('/lessons/bulk/delete', ['ids' => [$deletable->id, $other->id]])
            ->assertStatus(403);

        $this->assertNotSoftDeleted('lessons', ['id' => $deletable->id]);
        $this->assertNotSoftDeleted('lessons', ['id' => $other->id]);
    });
});

describe('bulk restore', function () {
    test('an admin can bulk restore lessons and restored_by is recorded', function () {
        $admin = $this->adminUser();
        $lessons = Lesson::factory()->count(3)->deleted()->create();
        $ids = $lessons->pluck('id')->all();

        $this->actingAs($admin)
            ->postJson('/lessons/bulk/restore', ['ids' => $ids])
            ->assertStatus(200)
            ->assertJson(['restored' => $ids, 'skipped' => []]);

        foreach ($ids as $id) {
            $restored = Lesson::query()->findOrFail($id);

            expect($restored->deleted_at)->toBeNull()
                ->and($restored->restored_by)->toBe($admin->id)
                ->and($restored->restored_at)->not->toBeNull();
        }
    });

    test('a non-admin with restore lessons is blocked by the bulkRestore ability', function () {
        $lessons = Lesson::factory()->count(2)->deleted()->create();

        $this->actingAs($this->lessonEditorUser())
            ->postJson('/lessons/bulk/restore', ['ids' => $lessons->pluck('id')->all()])
            ->assertStatus(403);

        foreach ($lessons as $lesson) {
            $this->assertSoftDeleted('lessons', ['id' => $lesson->id]);
        }
    });

    test('active and non-existent ids are skipped', function () {
        $admin = $this->adminUser();
        $trashed = Lesson::factory()->deleted()->create();
        $active = Lesson::factory()->create();

        $this->actingAs($admin)
            ->postJson('/lessons/bulk/restore', ['ids' => [$trashed->id, $active->id, 99999]])
            ->assertStatus(200)
            ->assertJson([
                'restored' => [$trashed->id],
                'skipped' => [$active->id, 99999],
            ]);
    });

    test('ids are capped at 100', function () {
        $admin = $this->adminUser();

        $this->actingAs($admin)
            ->postJson('/lessons/bulk/restore', ['ids' => range(1, 101)])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });
});
