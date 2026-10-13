<?php

declare(strict_types=1);

use App\Models\Course;
use App\Models\Log;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\CreatesUsers;

uses(
    LazilyRefreshDatabase::class,
    CreatesUsers::class,
);

describe('index', function () {
    test('authenticated user with permission can list courses', function () {
        $superAdmin = $this->superAdminUser();

        Course::factory()->count(3)->create();

        $this->actingAs($superAdmin)
            ->get('/courses')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Courses/Index')
                ->has('courses')
                ->has('permissions_meta')
            );
    });

    test('unauthenticated user cannot list courses', function () {
        $this->get('/courses')
            ->assertRedirect('/login');
    });

    test('user without permission cannot list courses', function () {
        $user = $this->userWithNoPermissions();

        $this->actingAs($user)
            ->get('/courses')
            ->assertStatus(403);
    });

    test('index can be filtered by difficulty', function () {
        $superAdmin = $this->superAdminUser();

        Course::factory()->count(2)->create(['difficulty' => 'beginner']);
        Course::factory()->create(['difficulty' => 'advanced']);

        $this->actingAs($superAdmin)
            ->get('/courses?difficulty=advanced')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Courses/Index')
                ->has('courses.data', 1)
            );
    });

    test('index can be searched by title', function () {
        $superAdmin = $this->superAdminUser();

        Course::factory()->create([
            'title' => 'Introduction to Laravel',
            'summary' => null,
        ]);
        Course::factory()->create([
            'title' => 'Advanced MySQL Indexing',
            'summary' => null,
        ]);

        $this->actingAs($superAdmin)
            ->get('/courses?search=Laravel')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->has('courses.data', 1)
            );
    });
});

describe('create', function () {
    test('authenticated user with permission can view create form', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/courses/create')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Courses/Create')
                ->has('difficulties')
                ->has('instructors')
            );
    });

    test('unauthenticated user cannot view create form', function () {
        $this->get('/courses/create')
            ->assertRedirect('/login');
    });

    test('user without permission cannot view create form', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->get('/courses/create')
            ->assertStatus(403);
    });
});

describe('store', function () {
    test('authenticated user with permission can create a course', function () {
        $superAdmin = $this->superAdminUser();

        $payload = [
            'title' => 'Introduction to Laravel',
            'summary' => 'A practical first course.',
            'description' => 'Covers routing, controllers and Eloquent.',
            'difficulty' => 'beginner',
            'estimated_duration_minutes' => 240,
            'instructor_id' => $superAdmin->id,
            'published_at' => '2026-11-01 09:00:00',
        ];

        $this->actingAs($superAdmin)
            ->postJson('/courses', $payload)
            ->assertStatus(201)
            ->assertJsonFragment(['title' => 'Introduction to Laravel']);

        $this->assertDatabaseHas('courses', [
            'title' => 'Introduction to Laravel',
            'difficulty' => 'beginner',
            'estimated_duration_minutes' => 240,
            'instructor_id' => $superAdmin->id,
        ]);
    });

    test('store redirects to the show page for non-JSON requests', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->post('/courses', [
                'title' => 'Redirect Course',
                'difficulty' => 'beginner',
            ])
            ->assertRedirect();

        $course = Course::where('title', 'Redirect Course')->firstOrFail();

        expect($course->id)->toBeInt();
    });

    test('user without permission cannot create a course', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->postJson('/courses', [
                'title' => 'Blocked Course',
                'difficulty' => 'beginner',
            ])
            ->assertStatus(403);
    });

    test('unauthenticated user cannot create a course', function () {
        $this->postJson('/courses', [
            'title' => 'Anonymous Course',
            'difficulty' => 'beginner',
        ])->assertStatus(401);
    });

    test('store fails validation when title is missing', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/courses', ['difficulty' => 'beginner'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    });

    test('store fails validation when title exceeds max length', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/courses', [
                'title' => str_repeat('a', 256),
                'difficulty' => 'beginner',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    });

    test('store fails validation when difficulty is invalid', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/courses', [
                'title' => 'Invalid Difficulty',
                'difficulty' => 'impossible',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['difficulty']);
    });

    test('store fails validation when duration is below the minimum', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/courses', [
                'title' => 'Zero Duration',
                'difficulty' => 'beginner',
                'estimated_duration_minutes' => 0,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['estimated_duration_minutes']);
    });

    test('store fails validation when duration exceeds one week in minutes', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/courses', [
                'title' => 'Too Long',
                'difficulty' => 'beginner',
                'estimated_duration_minutes' => 10081,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['estimated_duration_minutes']);
    });

    test('store fails validation when instructor does not exist', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/courses', [
                'title' => 'Missing Instructor',
                'difficulty' => 'beginner',
                'instructor_id' => 99999,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['instructor_id']);
    });

    test('store fails validation when published_at is not a date', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/courses', [
                'title' => 'Bad Date',
                'difficulty' => 'beginner',
                'published_at' => 'not-a-date',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['published_at']);
    });

    test('store succeeds with only required fields', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/courses', [
                'title' => 'Minimal Course',
                'difficulty' => 'intermediate',
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('courses', [
            'title' => 'Minimal Course',
            'difficulty' => 'intermediate',
            'summary' => null,
            'description' => null,
            'estimated_duration_minutes' => null,
            'instructor_id' => null,
            'published_at' => null,
        ]);
    });

    test('store generates a slug from the title', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/courses', [
                'title' => 'Vue Components In Depth',
                'difficulty' => 'advanced',
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('courses', [
            'title' => 'Vue Components In Depth',
            'slug' => 'vue-components-in-depth',
        ]);
    });

    test('store succeeds with meta data', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/courses', [
                'title' => 'Course with meta',
                'difficulty' => 'beginner',
                'meta' => ['featured' => true],
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('courses', ['title' => 'Course with meta']);
    });

    test('logs course creation with actor id', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/courses', [
                'title' => 'Logged Course',
                'difficulty' => 'beginner',
            ])
            ->assertStatus(201);

        $log = Log::query()
            ->where('action_id', Log::ACTION_CREATE_COURSE)
            ->where('logged_in_user_id', $superAdmin->id)
            ->first();

        expect($log)->not->toBeNull()
            ->and($log->data)->toHaveKey('after');
    });
});

describe('show', function () {
    test('authenticated user with permission can view a course', function () {
        $superAdmin = $this->superAdminUser();

        $course = Course::factory()->create();

        $this->actingAs($superAdmin)
            ->get("/courses/{$course->id}")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Courses/Show')
                ->has('course')
            );
    });

    test('unauthenticated user cannot view a course', function () {
        $course = Course::factory()->create();

        $this->get("/courses/{$course->id}")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view a course', function () {
        $user = $this->userWithNoPermissions();

        $course = Course::factory()->create();

        $this->actingAs($user)
            ->get("/courses/{$course->id}")
            ->assertStatus(403);
    });

    test('show returns 404 for a non-existent course', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/courses/99999')
            ->assertStatus(404);
    });
});

describe('edit', function () {
    test('authenticated user with permission can view edit form', function () {
        $superAdmin = $this->superAdminUser();

        $course = Course::factory()->create();

        $this->actingAs($superAdmin)
            ->get("/courses/{$course->id}/edit")
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Courses/Edit')
                ->has('course')
                ->has('difficulties')
                ->has('instructors')
            );
    });

    test('unauthenticated user cannot view edit form', function () {
        $course = Course::factory()->create();

        $this->get("/courses/{$course->id}/edit")
            ->assertRedirect('/login');
    });

    test('user without permission cannot view edit form', function () {
        $user = $this->normalUser();

        $course = Course::factory()->create();

        $this->actingAs($user)
            ->get("/courses/{$course->id}/edit")
            ->assertStatus(403);
    });
});

describe('update', function () {
    test('authenticated user with permission can update a course', function () {
        $superAdmin = $this->superAdminUser();

        $course = Course::factory()->create(['title' => 'Old Title']);

        $this->actingAs($superAdmin)
            ->putJson("/courses/{$course->id}", [
                'title' => 'New Title',
                'difficulty' => $course->difficulty->value,
            ])
            ->assertStatus(200)
            ->assertJsonFragment(['title' => 'New Title']);

        $this->assertDatabaseHas('courses', [
            'id' => $course->id,
            'title' => 'New Title',
        ]);
    });

    test('patch verb also updates a course', function () {
        $superAdmin = $this->superAdminUser();

        $course = Course::factory()->create(['summary' => 'Old summary']);

        $this->actingAs($superAdmin)
            ->patchJson("/courses/{$course->id}", ['summary' => 'New summary'])
            ->assertStatus(200)
            ->assertJsonFragment(['summary' => 'New summary']);
    });

    test('user without permission cannot update a course', function () {
        $user = $this->normalUser();

        $course = Course::factory()->create();

        $this->actingAs($user)
            ->putJson("/courses/{$course->id}", ['title' => 'Blocked'])
            ->assertStatus(403);
    });

    test('update fails validation when title is set to null', function () {
        $superAdmin = $this->superAdminUser();

        $course = Course::factory()->create();

        $this->actingAs($superAdmin)
            ->patchJson("/courses/{$course->id}", ['title' => null])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    });

    test('update fails validation when difficulty is invalid', function () {
        $superAdmin = $this->superAdminUser();

        $course = Course::factory()->create();

        $this->actingAs($superAdmin)
            ->patchJson("/courses/{$course->id}", ['difficulty' => 'impossible'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['difficulty']);
    });

    test('update fails validation when duration is out of range', function () {
        $superAdmin = $this->superAdminUser();

        $course = Course::factory()->create();

        $this->actingAs($superAdmin)
            ->patchJson("/courses/{$course->id}", ['estimated_duration_minutes' => 10081])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['estimated_duration_minutes']);
    });

    test('optional fields can be cleared by sending null', function () {
        $superAdmin = $this->superAdminUser();

        $course = Course::factory()->create([
            'summary' => 'To be cleared',
            'estimated_duration_minutes' => 60,
        ]);

        $this->actingAs($superAdmin)
            ->patchJson("/courses/{$course->id}", [
                'summary' => null,
                'estimated_duration_minutes' => null,
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('courses', [
            'id' => $course->id,
            'summary' => null,
            'estimated_duration_minutes' => null,
        ]);
    });

    test('omitted fields are not cleared on update', function () {
        $superAdmin = $this->superAdminUser();

        $course = Course::factory()->create([
            'title' => 'Original title',
            'summary' => 'Original summary.',
        ]);

        $this->actingAs($superAdmin)
            ->patchJson("/courses/{$course->id}", ['title' => 'Updated title'])
            ->assertStatus(200);

        $this->assertDatabaseHas('courses', [
            'id' => $course->id,
            'title' => 'Updated title',
            'summary' => 'Original summary.',
        ]);
    });

    test('update redirects to the show page for non-JSON requests', function () {
        $superAdmin = $this->superAdminUser();

        $course = Course::factory()->create();

        $this->actingAs($superAdmin)
            ->put("/courses/{$course->id}", [
                'title' => 'Redirected Update',
                'difficulty' => $course->difficulty->value,
            ])
            ->assertRedirect("/courses/{$course->id}");
    });

    test('logs course updates with actor id', function () {
        $actor = $this->adminUser();

        $course = Course::factory()->create(['title' => 'Old Title']);

        $this->actingAs($actor)
            ->putJson("/courses/{$course->id}", [
                'title' => 'New Title',
                'difficulty' => $course->difficulty->value,
            ])
            ->assertOk();

        $log = Log::query()
            ->where('action_id', Log::ACTION_UPDATE_COURSE)
            ->where('logged_in_user_id', $actor->id)
            ->first();

        expect($log)->not->toBeNull()
            ->and($log->data)->toHaveKeys(['before', 'after']);
    });
});

describe('destroy', function () {
    test('authenticated user with permission can soft delete a course', function () {
        $superAdmin = $this->superAdminUser();

        $course = Course::factory()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/courses/{$course->id}")
            ->assertStatus(204);

        $this->assertSoftDeleted('courses', ['id' => $course->id]);
    });

    test('user without permission cannot soft delete a course', function () {
        $user = $this->normalUser();

        $course = Course::factory()->create();

        $this->actingAs($user)
            ->deleteJson("/courses/{$course->id}")
            ->assertStatus(403);
    });

    test('destroy returns 404 for a non-existent course', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->deleteJson('/courses/99999')
            ->assertStatus(404);
    });

    test('destroy records the deleting user', function () {
        $superAdmin = $this->superAdminUser();

        $course = Course::factory()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/courses/{$course->id}")
            ->assertStatus(204);

        $this->assertDatabaseHas('courses', [
            'id' => $course->id,
            'deleted_by' => $superAdmin->id,
        ]);
    });
});

describe('restore', function () {
    test('authenticated user with permission can restore a soft-deleted course', function () {
        $superAdmin = $this->superAdminUser();

        $course = Course::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->postJson("/courses/{$course->id}/restore")
            ->assertStatus(204);

        $this->assertDatabaseHas('courses', [
            'id' => $course->id,
            'deleted_at' => null,
        ]);
    });

    test('user without permission cannot restore a course', function () {
        $user = $this->normalUser();

        $course = Course::factory()->deleted()->create();

        $this->actingAs($user)
            ->postJson("/courses/{$course->id}/restore")
            ->assertStatus(403);
    });

    test('restore returns 404 for a course that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();

        $course = Course::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson("/courses/{$course->id}/restore")
            ->assertStatus(404);
    });
});

describe('force delete', function () {
    test('authenticated user with permission can force delete a course', function () {
        $superAdmin = $this->superAdminUser();

        $course = Course::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/courses/{$course->id}/force")
            ->assertStatus(204);

        $this->assertDatabaseMissing('courses', ['id' => $course->id]);
    });

    test('user without permission cannot force delete a course', function () {
        $user = $this->normalUser();

        $course = Course::factory()->deleted()->create();

        $this->actingAs($user)
            ->deleteJson("/courses/{$course->id}/force")
            ->assertStatus(403);
    });

    test('force delete returns 404 for a course that is not soft-deleted', function () {
        $superAdmin = $this->superAdminUser();

        $course = Course::factory()->create();

        $this->actingAs($superAdmin)
            ->deleteJson("/courses/{$course->id}/force")
            ->assertStatus(404);
    });
});

describe('bulk delete', function () {
    test('authenticated user with permission can bulk soft delete courses', function () {
        $superAdmin = $this->superAdminUser();

        $courses = Course::factory()->count(3)->create();
        $ids = $courses->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson('/courses/bulk/delete', ['ids' => $ids])
            ->assertStatus(200)
            ->assertJson([
                'deleted' => $ids,
                'skipped' => [],
            ]);

        foreach ($ids as $id) {
            $this->assertSoftDeleted('courses', ['id' => $id]);
        }
    });

    test('bulk delete fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/courses/bulk/delete', ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk delete skips non-existent ids', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/courses/bulk/delete', ['ids' => [99999]])
            ->assertStatus(200)
            ->assertJson([
                'deleted' => [],
                'skipped' => [99999],
            ]);
    });

    test('user without permission cannot bulk delete courses', function () {
        $user = $this->normalUser();

        $courses = Course::factory()->count(2)->create();

        $this->actingAs($user)
            ->postJson('/courses/bulk/delete', [
                'ids' => $courses->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('bulk restore', function () {
    test('authenticated user with permission can bulk restore courses', function () {
        $superAdmin = $this->superAdminUser();

        $courses = Course::factory()->count(3)->deleted()->create();
        $ids = $courses->pluck('id')->all();

        $this->actingAs($superAdmin)
            ->postJson('/courses/bulk/restore', ['ids' => $ids])
            ->assertStatus(200)
            ->assertJson([
                'restored' => $ids,
                'skipped' => [],
            ]);

        foreach ($ids as $id) {
            $this->assertDatabaseHas('courses', [
                'id' => $id,
                'deleted_at' => null,
            ]);
        }
    });

    test('bulk restore fails validation with empty ids array', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/courses/bulk/restore', ['ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    });

    test('bulk restore skips non-existent ids', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/courses/bulk/restore', ['ids' => [99999]])
            ->assertStatus(200)
            ->assertJson([
                'restored' => [],
                'skipped' => [99999],
            ]);
    });

    test('user without permission cannot bulk restore courses', function () {
        $user = $this->normalUser();

        $courses = Course::factory()->count(2)->deleted()->create();

        $this->actingAs($user)
            ->postJson('/courses/bulk/restore', [
                'ids' => $courses->pluck('id')->all(),
            ])
            ->assertStatus(403);
    });
});

describe('export', function () {
    test('authenticated user with permission can export courses as csv', function () {
        $superAdmin = $this->superAdminUser();

        Course::factory()->count(2)->create();

        $response = $this->actingAs($superAdmin)
            ->get('/courses/export')
            ->assertStatus(200);

        expect($response->headers->get('content-type'))->toContain('text/csv');
    });

    test('export contains the course title', function () {
        $superAdmin = $this->superAdminUser();

        Course::factory()->create(['title' => 'Exported Course Title']);

        $response = $this->actingAs($superAdmin)
            ->get('/courses/export')
            ->assertStatus(200);

        expect($response->streamedContent())->toContain('Exported Course Title');
    });

    test('export neutralises spreadsheet formula injection', function () {
        $superAdmin = $this->superAdminUser();

        Course::factory()->create(['title' => '=HYPERLINK("http://example.com")']);

        $response = $this->actingAs($superAdmin)
            ->get('/courses/export')
            ->assertStatus(200);

        expect($response->streamedContent())->not->toContain(',=HYPERLINK')
            ->and($response->streamedContent())->not->toMatch('/^=HYPERLINK/m');
    });

    test('unauthenticated user cannot export courses', function () {
        $this->get('/courses/export')
            ->assertRedirect('/login');
    });

    test('user without permission cannot export courses', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->get('/courses/export')
            ->assertStatus(403);
    });
});

describe('import', function () {
    test('user without permission cannot preview an import', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->postJson('/courses/import/preview', [
                'file' => UploadedFile::fake()->createWithContent('courses.csv', "title,difficulty\nTest,beginner\n"),
            ])
            ->assertStatus(403);
    });

    test('preview fails validation when file is missing', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/courses/import/preview', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    });

    test('preview fails validation when file is not a csv', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/courses/import/preview', [
                'file' => UploadedFile::fake()->create('courses.pdf', 100, 'application/pdf'),
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    });

    test('preview returns a token without persisting any courses', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/courses/import/preview', [
                'file' => UploadedFile::fake()->createWithContent(
                    'courses.csv',
                    "title,difficulty\nImported Course,beginner\n"
                ),
            ])
            ->assertStatus(200)
            ->assertJsonStructure(['token']);

        $this->assertDatabaseMissing('courses', ['title' => 'Imported Course']);
    });

    test('commit fails validation when token is not a uuid', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/courses/import/commit', [
                'token' => 'not-a-uuid',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['token']);
    });

    test('commit fails validation when token is missing', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/courses/import/commit', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['token']);
    });

    test('user without permission cannot commit an import', function () {
        $user = $this->normalUser();

        $this->actingAs($user)
            ->postJson('/courses/import/commit', [
                'token' => Str::uuid()->toString(),
            ])
            ->assertStatus(403);
    });
});

describe('soft delete scoping', function () {
    test('index does not return soft-deleted courses', function () {
        $superAdmin = $this->superAdminUser();

        Course::factory()->count(2)->create();
        $trashed = Course::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->get('/courses')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Courses/Index')
                ->has('courses.data', 2)
            );

        $this->assertSoftDeleted('courses', ['id' => $trashed->id]);
    });

    test('show returns 404 for a soft-deleted course', function () {
        $superAdmin = $this->superAdminUser();

        $course = Course::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->get("/courses/{$course->id}")
            ->assertStatus(404);
    });

    test('index can include trashed courses when requested', function () {
        $superAdmin = $this->superAdminUser();

        Course::factory()->count(2)->create();
        Course::factory()->deleted()->create();

        $this->actingAs($superAdmin)
            ->get('/courses?trashed=with')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->has('courses.data', 3)
            );
    });
});
