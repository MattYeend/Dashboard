<?php

use App\Models\Attachment;
use App\Models\Company;
use App\Models\Log;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\Concerns\CreatesUsers;

uses(
    LazilyRefreshDatabase::class,
    CreatesUsers::class,
);

beforeEach(function () {
    setPermissionsTeamId(1);

    Role::firstOrCreate(['name' => 'Admin']);
    Role::firstOrCreate(['name' => 'Super Admin']);
    Role::firstOrCreate(['name' => 'User']);

    Storage::fake(Attachment::DISK);
});

describe('store', function () {
    test('authenticated user with permission can upload an attachment', function () {
        $superAdmin = $this->superAdminUser();
        $company = Company::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson('/attachments', [
                'attachable_type' => 'company',
                'attachable_id' => $company->id,
                'file' => UploadedFile::fake()->create('brochure.pdf', 500, 'application/pdf'),
            ])
            ->assertStatus(201)
            ->assertJsonFragment(['original_filename' => 'brochure.pdf']);

        $this->assertDatabaseHas('attachments', [
            'attachable_type' => $company->getMorphClass(),
            'attachable_id' => $company->id,
            'original_filename' => 'brochure.pdf',
            'mime_type' => 'application/pdf',
        ]);
    });

    test('unauthenticated user cannot upload an attachment', function () {
        $company = Company::factory()->create();

        $this->postJson('/attachments', [
            'attachable_type' => 'company',
            'attachable_id' => $company->id,
            'file' => UploadedFile::fake()->create('brochure.pdf', 500, 'application/pdf'),
        ])->assertStatus(401);

        $this->assertDatabaseCount('attachments', 0);
    });

    test('user without permission cannot upload an attachment', function () {
        $user = $this->userWithNoPermissions();
        $company = Company::factory()->create();

        $this->actingAs($user)
            ->postJson('/attachments', [
                'attachable_type' => 'company',
                'attachable_id' => $company->id,
                'file' => UploadedFile::fake()->create('brochure.pdf', 500, 'application/pdf'),
            ])
            ->assertStatus(403);

        $this->assertDatabaseCount('attachments', 0);
    });

    test('store fails validation when file is missing', function () {
        $superAdmin = $this->superAdminUser();
        $company = Company::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson('/attachments', [
                'attachable_type' => 'company',
                'attachable_id' => $company->id,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    });

    test('store fails validation when attachable_type is unrecognised', function () {
        $superAdmin = $this->superAdminUser();
        $company = Company::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson('/attachments', [
                'attachable_type' => 'not-a-real-type',
                'attachable_id' => $company->id,
                'file' => UploadedFile::fake()->create('brochure.pdf', 500, 'application/pdf'),
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['attachable_type']);
    });

    test('store fails validation when attachable_id does not exist', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->postJson('/attachments', [
                'attachable_type' => 'company',
                'attachable_id' => 999999,
                'file' => UploadedFile::fake()->create('brochure.pdf', 500, 'application/pdf'),
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['attachable_id']);
    });

    test('store rejects a disallowed mime type', function () {
        $superAdmin = $this->superAdminUser();
        $company = Company::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson('/attachments', [
                'attachable_type' => 'company',
                'attachable_id' => $company->id,
                'file' => UploadedFile::fake()->create('script.php', 10, 'application/x-php'),
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['file']);

        $this->assertDatabaseCount('attachments', 0);
    });

    test('store rejects a file exceeding the maximum size', function () {
        $superAdmin = $this->superAdminUser();
        $company = Company::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson('/attachments', [
                'attachable_type' => 'company',
                'attachable_id' => $company->id,
                'file' => UploadedFile::fake()->create('large.pdf', 20000, 'application/pdf'),
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    });

    test('stored file is written to the private disk under a randomised name', function () {
        $superAdmin = $this->superAdminUser();
        $company = Company::factory()->create();

        $this->actingAs($superAdmin)
            ->postJson('/attachments', [
                'attachable_type' => 'company',
                'attachable_id' => $company->id,
                'file' => UploadedFile::fake()->create('brochure.pdf', 500, 'application/pdf'),
            ])
            ->assertStatus(201);

        $attachment = Attachment::firstOrFail();

        expect($attachment->disk_path)->not->toBe('brochure.pdf');

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk(Attachment::DISK);
        $disk->assertExists($attachment->disk_path);
    });

    test('logs attachment creation with actor id', function () {
        $actor = $this->adminUser();
        $company = Company::factory()->create();

        $this->actingAs($actor)
            ->postJson('/attachments', [
                'attachable_type' => 'company',
                'attachable_id' => $company->id,
                'file' => UploadedFile::fake()->create('brochure.pdf', 500, 'application/pdf'),
            ])
            ->assertStatus(201);

        $log = Log::query()
            ->where('action_id', Log::ACTION_CREATE_ATTACHMENT)
            ->where('logged_in_user_id', $actor->id)
            ->first();

        expect($log)->not->toBeNull()
            ->and($log->data)->toHaveKeys(['after']);
    });
});

describe('download', function () {
    test('authenticated user with permission can download an attachment', function () {
        $superAdmin = $this->superAdminUser();
        $company = Company::factory()->create();

        Storage::disk(Attachment::DISK)->put('test-file.pdf', 'fake pdf contents');

        $attachment = Attachment::factory()->forModel($company)->create([
            'disk_path' => 'test-file.pdf',
            'original_filename' => 'brochure.pdf',
            'mime_type' => 'application/pdf',
        ]);

        $response = $this->actingAs($superAdmin)
            ->get("/attachments/{$attachment->id}/download");

        $response->assertStatus(200)
            ->assertHeader('content-disposition');

        expect($response->headers->get('content-disposition'))->toContain('brochure.pdf');
    });

    test('unauthenticated user cannot download an attachment', function () {
        $company = Company::factory()->create();
        $attachment = Attachment::factory()->forModel($company)->create();

        $this->get("/attachments/{$attachment->id}/download")
            ->assertRedirect('/login');
    });

    test('user without permission cannot download an attachment', function () {
        $user = $this->userWithNoPermissions();
        $company = Company::factory()->create();
        $attachment = Attachment::factory()->forModel($company)->create();

        $this->actingAs($user)
            ->get("/attachments/{$attachment->id}/download")
            ->assertStatus(403);
    });

    test('download returns 404 for a non-existent attachment', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->get('/attachments/999999/download')
            ->assertStatus(404);
    });

    test('download returns 404 for a soft-deleted attachment', function () {
        $superAdmin = $this->superAdminUser();
        $company = Company::factory()->create();
        $attachment = Attachment::factory()->forModel($company)->deleted()->create();

        $this->actingAs($superAdmin)
            ->get("/attachments/{$attachment->id}/download")
            ->assertStatus(404);
    });
});

describe('destroy', function () {
    test('authenticated user with permission can soft delete an attachment', function () {
        $superAdmin = $this->superAdminUser();
        $company = Company::factory()->create();

        Storage::disk(Attachment::DISK)->put('test-file.pdf', 'fake pdf contents');

        $attachment = Attachment::factory()->forModel($company)->create([
            'disk_path' => 'test-file.pdf',
        ]);

        $this->actingAs($superAdmin)
            ->deleteJson("/attachments/{$attachment->id}")
            ->assertStatus(204);

        $this->assertSoftDeleted('attachments', ['id' => $attachment->id]);

        // Soft delete must not touch the underlying file.
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk(Attachment::DISK);
        $disk->assertExists('test-file.pdf');
    });

    test('user without permission cannot delete an attachment', function () {
        $user = $this->userWithNoPermissions();
        $company = Company::factory()->create();
        $attachment = Attachment::factory()->forModel($company)->create();

        $this->actingAs($user)
            ->deleteJson("/attachments/{$attachment->id}")
            ->assertStatus(403);

        $this->assertDatabaseHas('attachments', ['id' => $attachment->id, 'deleted_at' => null]);
    });

    test('destroy returns 404 for a non-existent attachment', function () {
        $superAdmin = $this->superAdminUser();

        $this->actingAs($superAdmin)
            ->deleteJson('/attachments/999999')
            ->assertStatus(404);
    });

    test('logs attachment deletion with actor id', function () {
        $actor = $this->adminUser();
        $company = Company::factory()->create();
        $attachment = Attachment::factory()->forModel($company)->create();

        $this->actingAs($actor)
            ->deleteJson("/attachments/{$attachment->id}")
            ->assertStatus(204);

        $log = Log::query()
            ->where('action_id', Log::ACTION_DELETE_ATTACHMENT)
            ->where('logged_in_user_id', $actor->id)
            ->first();

        expect($log)->not->toBeNull()
            ->and($log->data)->toHaveKeys(['before']);
    });
});
